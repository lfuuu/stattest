<?php
namespace app\classes\grid\account\telecom\maintenance;

use app\models\Bill;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use app\models\ClientAccount;
use app\models\ClientContract;

class BlockBillPayOverdueFolder extends AccountGridFolder
{
    public $block_date;

    public $_isGenericFolder = false;

    /**
     * @return string
     */
    public function getName()
    {
        return 'Просрочка платежа';
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

        $queryBlockDate = (new Query())
            ->select(new Expression('MIN(b.pay_bill_until)'))
            ->from(['b' => Bill::tableName()])
            ->where('c.id = b.client_id')
            ->andWhere(['b.is_pay_overdue' => 1]);

        $query->addSelect(['block_date' => $queryBlockDate]);

        $statQuery = (new Query)
            ->select('clients.id')
            ->from(['clients' => ClientAccount::tableName()])
            ->innerJoin(['contracts' => ClientContract::tableName()], 'clients.contract_id = contracts.id')
            ->andWhere(['contracts.business_id' => $this->grid->getBusiness()])
            ->andWhere(['contracts.business_process_status_id' => $this->getBusinessProcessStatus()])
            ->andWhere(['clients.is_bill_pay_overdue' => 1])
            ->andWhere(['clients.is_active' => 1]);

        $query->where(['IN', 'c.id', $statQuery]);
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