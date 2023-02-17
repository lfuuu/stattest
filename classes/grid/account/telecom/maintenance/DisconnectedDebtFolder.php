<?php
namespace app\classes\grid\account\telecom\maintenance;

use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use Yii;
use yii\db\Query;


class DisconnectedDebtFolder extends AccountGridFolder
{
    public $_isGenericFolder = false;

    public function getName()
    {
        return 'Отключенные за долги';
    }

    public function getColumns()
    {
        return [
            'status',
            'id',
            'company',
            'currency',
            'manager',
            'region',
            'legal_entity',
        ];
    }

    public function queryParams(Query $query)
    {
        parent::queryParams($query);

        $query->andWhere(['cr.business_id' => $this->grid->getBusiness()]);
        $query->andWhere(['cr.business_process_status_id' => $this->getBusinessProcessStatus()]);
        $query->andWhere(['c.is_blocked' => 1]);
    }

    /**
     * Получение статуса бизнес процесса
     *
     * @return int
     */
    protected function getBusinessProcessStatus()
    {
        return BusinessProcessStatus::TELEKOM_MAINTENANCE_WORK;
    }

}