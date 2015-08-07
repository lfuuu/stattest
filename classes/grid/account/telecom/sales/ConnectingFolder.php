<?php
namespace app\classes\grid\account\telecom\sales;

use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use Yii;
use yii\db\Query;


class ConnectingFolder extends AccountGridFolder
{
    public function getName()
    {
        return 'Подключаемые';
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
        $query->andWhere(['c.status' => 'connecting']);
    }

}