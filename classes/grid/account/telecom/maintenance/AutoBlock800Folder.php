<?php
namespace app\classes\grid\account\telecom\maintenance;

use app\models\UsageVoip;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use app\models\billing\Clients;
use app\models\billing\CachedCounter;
use app\models\billing\Locks;

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

        $billingQuery = (new Query)
            ->select('clients.id')
            ->from(['clients' => Clients::tableName()])
            ->innerJoin(['counter' => CachedCounter::tableName()], 'counter.client_id = clients.id')
            ->leftJoin(['lock' => Locks::tableName()], 'lock.client_id = clients.id')
            ->where(new Expression('TRUE IN (lock.voip_auto_disabled, lock.voip_auto_disabled_local)'));

        $clientsIDs = $billingQuery->column(Clients::getDb());

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