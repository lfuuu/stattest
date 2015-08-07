<?php
namespace app\classes\grid\account\welltime\maintenance;

use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use Yii;
use yii\db\Query;


class MaintenanceFreeFolder extends AccountGridFolder
{
    public function getName()
    {
        return 'Без Техобслуживания';
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
        $query->andWhere(['cr.business_process_status_id' => BusinessProcessStatus::WELLTIME_MAINTENANCE_MAINTENANCE_FREE]);
    }
}