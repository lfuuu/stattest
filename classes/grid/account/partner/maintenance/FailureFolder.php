<?php
namespace app\classes\grid\account\partner\maintenance;

use Yii;
use yii\db\Query;
use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;

class FailureFolder extends AccountGridFolder
{
    use PartherMaintanceTrait;

    public function getName()
    {
        return 'Отказ';
    }

    public function getColumns()
    {
        return [
            'status',
            'id',
            'company',
            'contractNo',
            'contract_created',
            'manager',
            'account_manager',
            'region',
            'contract_type',
            'partner_clients_service'
        ];
    }

    public function queryParams(Query $query)
    {
        parent::queryParams($query);

        $query->andWhere(['cr.business_id' => $this->grid->getBusiness()]);
        $query->andWhere(['cr.business_process_status_id' => BusinessProcessStatus::PARTNER_MAINTENANCE_FAILURE]);

        $this->extendQuery($query);
    }

    protected function getDefaultColumns()
    {
        return $this->appendServiceColumn(parent::getDefaultColumns());
    }
}