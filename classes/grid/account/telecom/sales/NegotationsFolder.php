<?php
namespace app\classes\grid\account\telecom\sales;

use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use Yii;
use yii\db\Query;


class NegotationsFolder extends AccountGridFolder
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
            'sale_channel',
            'manager',
            'region',
        ];
    }

    public function queryParams(Query $query)
    {
        parent::queryParams($query);

        $query->andWhere(['cr.contract_subdivision_id' => $this->grid->getContractSubdivision()]);
        $query->andWhere(['c.status' => 'negotiations']);
    }

}