<?php
namespace app\classes\grid\account\telecom\maintenance;

use app\classes\grid\account\AccountGridFolder;
use app\models\ClientBPStatuses;
use Yii;
use yii\db\Query;


class DisconnectedDebtFolder extends AccountGridFolder
{
    public function getName()
    {
        return 'Отключенные за долги';
    }

    public function getColumns()
    {
        return [
            'status',
            'id',
            'company',
            //'block_date',
            'currency',
            'manager',
            'region',
        ];
    }

    public function queryParams(Query $query)
    {
        parent::queryParams($query);

        $query->andWhere(['cr.contract_type_id' => $this->grid->getContractType()]);
        $query->andWhere(['cr.business_process_status_id' => ClientBPStatuses::TELEKOM_MAINTENANCE_WORK]);
        $query->andWhere(['c.is_blocked' => 1]);
    }
}