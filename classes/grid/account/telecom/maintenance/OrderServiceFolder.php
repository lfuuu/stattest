<?php
namespace app\classes\grid\account\telecom\maintenance;

use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use Yii;
use yii\db\Query;


class OrderServiceFolder extends AccountGridFolder
{
    public function getName()
    {
        return 'Заказ услуг';
    }

    public function getColumns()
    {
        return [
            'status',
            'id',
            'company',
            'created',
            'sale_channel',
            'manager',
            'region',
        ];
    }

    public function queryParams(Query $query)
    {
        parent::queryParams($query);

        $query->andWhere(['cr.business_id' => $this->grid->getBusiness()]);
        $query->andWhere(['cr.business_process_status_id' => BusinessProcessStatus::TELEKOM_MAINTENANCE_ORDER_OF_SERVICES]);
    }
}