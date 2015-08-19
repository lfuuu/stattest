<?php
namespace app\classes\grid\account\telecom\maintenance;

use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use Yii;
use yii\db\Query;


class DuplicateFolder extends AccountGridFolder
{
    public function getName()
    {
        return 'Дубликат';
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
        $query->andWhere(['cr.business_process_status_id' => BusinessProcessStatus::TELEKOM_MAINTENANCE_DUPLICATE]);
    }
}