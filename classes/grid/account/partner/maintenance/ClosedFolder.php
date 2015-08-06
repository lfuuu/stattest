<?php
namespace app\classes\grid\account\partner\maintenance;

use app\classes\grid\account\AccountGridFolder;
use app\models\ClientBPStatuses;
use Yii;
use yii\db\Query;


class ClosedFolder extends AccountGridFolder
{
    public function getName()
    {
        return 'Закрытые';
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
        $query->andWhere(['cr.business_process_status_id' => ClientBPStatuses::PARTNER_MAINTENANCE_CLOSED]);
    }
}