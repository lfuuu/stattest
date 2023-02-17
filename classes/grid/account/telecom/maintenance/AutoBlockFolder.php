<?php

namespace app\classes\grid\account\telecom\maintenance;

use app\classes\grid\account\AccountGridFolder;
use app\models\billing\Locks;
use app\models\BusinessProcessStatus;
use yii\db\Query;

class AutoBlockFolder extends AccountGridFolder
{
    public $block_date;

    public $_isGenericFolder = false;

    /**
     * @return string
     */
    public function getName()
    {
        return 'Автоблокировка';
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

        $query->andWhere(['cr.business_id' => $this->grid->getBusiness()]);
        $query->andWhere(['cr.business_process_status_id' => $this->getBusinessProcessStatus()]);
        $query->andWhere(['c.is_blocked' => 0]);

        try {
            Locks::setPgTimeout(Locks::PG_ACCOUNT_TIMEOUT);
            $clientsIDs = Locks::getFinanceLocks();
        } catch (\Exception $e) {
            $clientsIDs = [];
        }

        if (count($clientsIDs)) {
            $query->andWhere(['IN', 'c.id', $clientsIDs]);
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