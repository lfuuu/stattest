<?php
namespace app\classes\grid\account\telecom\sales;

use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use Yii;
use yii\db\Query;


class IncomingFolder extends AccountGridFolder
{
    public function getName()
    {
        return 'Входящие';
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

        $query->andWhere(['cr.contract_subdivision_id' => $this->grid->getContractSubdivision()]);
        $query->andWhere(['c.status' => 'income']);
    }

}