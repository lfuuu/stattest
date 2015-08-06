<?php
namespace app\classes\grid\account\provider\maintenance;

use app\classes\grid\account\AccountGridFolder;
use app\models\ClientBPStatuses;
use Yii;
use yii\db\Query;


class VOLSFolder extends AccountGridFolder
{
    public function getName()
    {
        return 'ВОЛС';
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
        $query->andWhere(['cr.business_process_status_id' => ClientBPStatuses::PROVIDER_MAINTENANCE_VOLS]);
    }
}