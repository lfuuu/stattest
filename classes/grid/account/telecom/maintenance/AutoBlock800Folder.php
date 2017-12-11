<?php

namespace app\classes\grid\account\telecom\maintenance;

use app\classes\grid\account\AccountGridFolder;
use app\models\billing\Locks;
use app\models\BusinessProcessStatus;
use app\models\UsageVoip;
use yii\db\Expression;
use yii\db\Query;

class AutoBlock800Folder extends AccountGridFolder
{
    public $block_date;

    /**
     * @return string
     */
    public function getName()
    {
        return '800-Блок';
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            'status',
            'id',
            'company',
            'currency',
            'block_date',
            'manager',
            'region',
            'legal_entity',
        ];
    }

    /**
     * @param Query $query
     */
    public function queryParams(Query $query)
    {
        parent::queryParams($query);

        $query->addSelect(['block_date' => $this->getBlockDateQuery()]);

        $query->leftJoin(['uv' => UsageVoip::tableName()], 'uv.client = c.client AND CAST(NOW() AS DATE) BETWEEN uv.actual_from AND uv.actual_to');
        $query->andWhere(['cr.business_id' => $this->grid->getBusiness()]);
        $query->andWhere(['cr.business_process_status_id' => $this->getBusinessProcessStatus()]);
        $query->andWhere(['c.is_blocked' => 0]);
        $query->andWhere('uv.line7800_id');

        try {
            Locks::setPgTimeout(Locks::PG_ACCOUNT_TIMEOUT);
            $clientsIDs = Locks::getVoipLocks();
        } catch (\Exception $e) {
            $clientsIDs = [];
        }

        if (count($clientsIDs)) {
            $query->andWhere(['IN', 'c.id', $clientsIDs]);
        } else {
            $query->andWhere(new Expression('false'));
        }
    }

    /**
     * Получение статуса бизнес процесса
     *
     * @return int
     */
    protected function getBusinessProcessStatus()
    {
        return BusinessProcessStatus::TELEKOM_MAINTENANCE_WORK;
    }
}