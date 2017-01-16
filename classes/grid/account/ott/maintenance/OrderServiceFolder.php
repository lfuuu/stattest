<?php
namespace app\classes\grid\account\ott\maintenance;

use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use Yii;
use yii\db\Query;


/**
 * Class OrderServiceFolder
 */
class OrderServiceFolder extends AccountGridFolder
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Заказ услуг';
    }

    /**
     * @return array
     */
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
            'legal_entity',
        ];
    }

    /**
     * @param Query $query
     */
    public function queryParams(Query $query)
    {
        parent::queryParams($query);

        $query->andWhere(['cr.business_id' => $this->grid->getBusiness()]);
        $query->andWhere(['cr.business_process_status_id' => BusinessProcessStatus::OTT_MAINTENANCE_ORDER_OF_SERVICES]);
    }
}