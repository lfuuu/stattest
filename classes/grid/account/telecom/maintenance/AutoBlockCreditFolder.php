<?php
namespace app\classes\grid\account\telecom\maintenance;

use yii\db\Expression;
use yii\db\Query;
use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use app\models\billing\Clients;

class AutoBlockCreditFolder extends AccountGridFolder
{
    public $block_date;

    public $_isGenericFolder = false;

    /**
     * @return string
     */
    public function getName()
    {
        return 'Фин. Блок';
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
            'currency',
            'block_date',
            'account_manager',
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
        $query->andWhere(['cr.business_process_status_id' => $this->getBusinessProcessStatus()]);
        $query->andWhere(['c.is_blocked' => 0]);


        $clientsIDs = Clients::find()
            ->select([Clients::tableName() . '.id'])
            ->joinWith('counter', true, 'INNER JOIN')
            ->andWhere(new Expression('clients.credit < -clients.balance'))
            ->column();

        if (count($clientsIDs)) {
            $query->addSelect(['block_date' => $this->getBlockDateQuery()]);

            $query->andWhere(['IN', 'c.id', $clientsIDs]);
        } else {
            $query->andWhere(new Expression('false'));
        }
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