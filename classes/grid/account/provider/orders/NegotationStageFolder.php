<?php
namespace app\classes\grid\account\provider\orders;

use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use Yii;
use yii\db\Query;


class NegotationStageFolder extends AccountGridFolder
{
    public function getName()
    {
        return 'В стадии переговоров';
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

        $query->andWhere(['cr.business_id' => $this->grid->getBusiness()]);
        $query->andWhere(['cr.business_process_status_id' => BusinessProcessStatus::PROVIDER_ORDERS_NEGOTIATION_STAGE]);
    }
}