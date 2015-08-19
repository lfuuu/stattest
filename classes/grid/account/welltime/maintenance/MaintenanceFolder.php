<?php
namespace app\classes\grid\account\welltime\maintenance;

use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use Yii;
use yii\db\Query;


class MaintenanceFolder extends AccountGridFolder
{
    public function getName()
    {
        return 'Техобслуживание';
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
        $query->andWhere(['cr.business_process_status_id' => BusinessProcessStatus::WELLTIME_MAINTENANCE_MAINTENANCE]);
    }
}