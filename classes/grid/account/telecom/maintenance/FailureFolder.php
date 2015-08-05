<?php
namespace app\classes\grid\account\telecom\maintenance;

use app\classes\grid\account\AccountGridFolder;
use app\models\ClientBPStatuses;
use Yii;
use yii\db\Query;


class FailureFolder extends AccountGridFolder
{
    public function getName()
    {
        return 'Отказ';
    }

    public function queryParams(Query $query)
    {
        parent::queryParams($query);

        $query->andWhere(['cr.contract_type_id' => $this->grid->getContractType()]);
        $query->andWhere(['cr.business_process_status_id' => ClientBPStatuses::TELEKOM_MAINTENANCE_FAILURE]);
    }

    public function getCount()
    {
        return null;
    }

}