<?php
namespace app\classes\grid\account\operator\operators;

use Yii;
use yii\db\Query;
use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;

class FormalFolder extends AccountGridFolder
{
    public function getName()
    {
        return 'Формальные';
    }

    public function getColumns()
    {
        return [
            'status',
            'id',
            'company',
            'contractNo',
            'contract_created',
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
        $query->andWhere(['cr.business_process_status_id' => BusinessProcessStatus::OPERATOR_CLIENTS_FORMAL]);
    }

}