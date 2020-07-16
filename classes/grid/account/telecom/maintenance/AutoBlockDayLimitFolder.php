<?php
namespace app\classes\grid\account\telecom\maintenance;

use Yii;
use yii\db\Expression;
use yii\db\Query;
use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use app\models\billing\Clients;
use app\models\billing\CachedCounter;
use app\models\ClientAccount;
use app\models\ClientContract;

class AutoBlockDayLimitFolder extends AccountGridFolder
{
    public $block_date;

    public $_isGenericFolder = false;

    /**
     * @return string
     */
    public function getName()
    {
        return 'Сут./МН. Блок';
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

        $billingQuery = (new Query)
                ->select('clients.id')
                ->from(['clients' => Clients::tableName()])
                ->innerJoin(['counter' => CachedCounter::tableName()], 'counter.client_id = clients.id')
                ->orWhere([
                    'OR',
                    [
                        'AND',
                        'clients.voip_limit_day < counter.amount_day_sum',
                        'clients.voip_limit_day > 0'
                    ],
                    [
                        'AND',
                        'clients.voip_limit_mn_day < counter.amount_mn_day_sum',
                        'clients.voip_limit_mn_day > 0'
                    ]
                ]);

        $clientsIDs = (array)$billingQuery->column(Clients::getDb());

        $statBillingQuery = (new Query)
                ->select('clients.id')
                ->from(['clients' => ClientAccount::tableName()])
                ->innerJoin(['contracts' => ClientContract::tableName()], 'clients.contract_id = contracts.id')
                ->andWhere(['contracts.business_id' => $this->grid->getBusiness()])
                ->andWhere(['contracts.business_process_status_id' => $this->getBusinessProcessStatus()])
                ->andWhere(['IN', 'clients.id', $clientsIDs]);

        $statQuery = (new Query)
                ->select('clients.id')
                ->from(['clients' => ClientAccount::tableName()])
                ->innerJoin(['contracts' => ClientContract::tableName()], 'clients.contract_id = contracts.id')
                ->andWhere(['contracts.business_id' => $this->grid->getBusiness()])
                ->andWhere(['contracts.business_process_status_id' => $this->getBusinessProcessStatus()])
                ->andWhere(['clients.voip_disabled' => 1])
                ->andWhere(['clients.is_bill_pay_overdue' => 0]);

        $resultIDs = array_merge($statBillingQuery->column(), $statQuery->column());

        if (count($resultIDs)) {
            $query->addSelect(['block_date' => $this->getBlockDateQuery()]);

            $query->where(['IN', 'c.id', $resultIDs]);
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