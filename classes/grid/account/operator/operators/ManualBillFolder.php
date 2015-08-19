<?php
namespace app\classes\grid\account\operator\operators;

use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use Yii;
use yii\db\Query;


class ManualBillFolder extends AccountGridFolder
{
    public function getName()
    {
        return 'Ручной счет';
    }

    public function getColumns()
    {
        return [
            'status',
            'id',
            'company',
            'created',
            'currency',
            'sale_channel',
            'manager',
            'region',
        ];
    }

    public function queryParams(Query $query)
    {
        parent::queryParams($query);

        $query->andWhere(['cr.contract_subdivision_id' => $this->grid->getContractSubdivision()]);
        $query->andWhere(['cr.business_process_status_id' => BusinessProcessStatus::OPERATOR_OPERATORS_MANUAL_BILL]);
    }
}