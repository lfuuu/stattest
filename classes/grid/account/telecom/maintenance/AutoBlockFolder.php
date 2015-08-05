<?php
namespace app\classes\grid\account\telecom\maintenance;

use app\classes\grid\account\AccountGridFolder;
use app\models\ClientBPStatuses;
use Yii;
use yii\db\Query;


class AutoBlockFolder extends AccountGridFolder
{
    public function getName()
    {
        return 'Автоблокировка';
    }

    public function getColumns()
    {
        return [
            'status',
            'id',
            'company',
            'currency',
            //'block_date',
            'manager',
            'region',
        ];
    }

    public function queryParams(Query $query)
    {
        parent::queryParams($query);

        $query->andWhere(['cr.contract_type_id' => $this->grid->getContractType()]);
        $query->andWhere([
            'not in',
            'cr.business_process_status_id',
            [
                ClientBPStatuses::TELEKOM_MAINTENANCE_CONNECTED,
                ClientBPStatuses::TELEKOM_MAINTENANCE_DISCONNECTED_DEBT,
            ],
        ]);
        $query->andWhere(['c.is_blocked' => 0]);
    }
}