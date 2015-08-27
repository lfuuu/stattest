<?php
namespace app\classes\grid\account\telecom\sales;

use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use Yii;
use yii\db\Query;


class TrashFolder extends AccountGridFolder
{
    public function getName()
    {
        return 'Мусор';
    }

    public function getCount()
    {
        return null;
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
        $query->andWhere(['in', 'c.status', ['double', 'trash']]);
    }

}