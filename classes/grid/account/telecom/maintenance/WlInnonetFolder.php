<?php

namespace app\classes\grid\account\telecom\maintenance;

use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use yii\db\Query;


class WlInnonetFolder extends AccountGridFolder
{
    /**
     * Название папки
     *
     * @return string
     */
    public function getName()
    {
        return 'WL_Innonet';
    }

    /**
     * Колонки отчета
     *
     * @return array
     */
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
            'legal_entity',
        ];
    }

    /**
     * Фильтр папки
     *
     * @param Query $query
     */
    public function queryParams(Query $query)
    {
        parent::queryParams($query);

        $query->andWhere([
            'cr.business_id' => $this->grid->getBusiness(),
            'cr.business_process_status_id' => BusinessProcessStatus::TELEKOM_MAINTENANCE_WLINNONET,
        ]);
    }
}