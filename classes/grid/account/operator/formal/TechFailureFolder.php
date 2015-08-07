<?php
namespace app\classes\grid\account\operator\formal;

use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use Yii;
use yii\db\Query;


class TechFailureFolder extends AccountGridFolder
{
    public function getName()
    {
        return 'Техотказ';
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

        $query->andWhere(['cr.contract_type_id' => $this->grid->getContractType()]);
        $query->andWhere(['cr.business_process_status_id' => BusinessProcessStatus::OPERATOR_FORMAL_TECH_FAILURE]);
    }
}