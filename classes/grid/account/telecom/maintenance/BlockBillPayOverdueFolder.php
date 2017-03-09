<?php
namespace app\classes\grid\account\telecom\maintenance;

use Yii;
use yii\db\Expression;
use yii\db\Query;
use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use app\models\billing\Clients;
use app\models\billing\Counter;
use app\models\ClientAccount;
use app\models\ClientContract;

class BlockBillPayOverdueFolder extends AccountGridFolder
{
    public $block_date;

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

        $statQuery = (new Query)
            ->select('clients.id')
            ->from(['clients' => ClientAccount::tableName()])
            ->innerJoin(['contracts' => ClientContract::tableName()], 'clients.contract_id = contracts.id')
            ->andWhere(['contracts.business_id' => $this->grid->getBusiness()])
            ->andWhere(['contracts.business_process_status_id' => BusinessProcessStatus::TELEKOM_MAINTENANCE_WORK])
            ->andWhere(['clients.is_bill_pay_overdue' => 1]);

        $query->where(['IN', 'c.id', $statQuery]);
    }
}