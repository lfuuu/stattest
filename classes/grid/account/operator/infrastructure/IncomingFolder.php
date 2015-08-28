<?php
namespace app\classes\grid\account\operator\infrastructure;

use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use Yii;
use yii\db\Query;


class IncomingFolder extends AccountGridFolder
{
    public function getName()
    {
        return 'Входящий';
    }

    public function getColumns()
    {
        return [
            'status',
            'id',
            'company',
            'created',
            'currency',
            'manager',
            'account_manager',
            'region',
            'federal_district',
            'contract_type',
            'financial_type'
        ];
    }

    public function queryParams(Query $query)
    {
        parent::queryParams($query);

        $query->andWhere(['cr.business_id' => $this->grid->getBusiness()]);
        $query->andWhere(['cr.business_process_status_id' => BusinessProcessStatus::OPERATOR_INFRASTRUCTURE_INCOMING]);
    }
}