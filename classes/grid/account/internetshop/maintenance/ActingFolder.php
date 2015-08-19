<?php
namespace app\classes\grid\account\internetshop\maintenance;

use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use Yii;
use yii\db\Query;


class ActingFolder extends AccountGridFolder
{
    public function getName()
    {
        return 'Действующий';
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
        $query->andWhere(['not in', 'c.status', ['double', 'trash']]);
    }
}