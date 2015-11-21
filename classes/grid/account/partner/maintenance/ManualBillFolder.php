<?php
namespace app\classes\grid\account\partner\maintenance;

use Yii;
use yii\db\Query;
use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;

class ManualBillFolder extends AccountGridFolder
{
    use PartherMaintanceTrait;

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
            'contractNo',
            'contract_created',
            'manager',
            'account_manager',
            'region',
            'contract_type',
            'service'
        ];
    }

    public function queryParams(Query $query)
    {
        parent::queryParams($query);

        $query->andWhere(['cr.business_id' => $this->grid->getBusiness()]);
        $query->andWhere(['cr.business_process_status_id' => BusinessProcessStatus::PARTNER_MAINTENANCE_MANUAL_BILL]);

        $this->extendQuery($query);
    }

    protected function getDefaultColumns()
    {
        return $this->appendServiceColumn(parent::getDefaultColumns());
    }
}