<?php
namespace app\classes\grid\account\operator\operators;

use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use Yii;
use yii\db\Query;


class AutoBlockedFolder extends AccountGridFolder
{
    public function getName()
    {
        return 'Автоблокировка';
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
        $query->andWhere(['cr.business_process_status_id' => BusinessProcessStatus::OPERATOR_OPERATORS_AUTO_BLOCKED]);

        $pg_query = new Query();
        $pg_query->select('client_id')->from('billing.locks')->where('voip_auto_disabled=true');
        $ids = $pg_query->column(\Yii::$app->dbPg);
        if (!empty($ids)) {
            $query->andWhere(['in', 'c.id', $ids]);
        }
    }
}