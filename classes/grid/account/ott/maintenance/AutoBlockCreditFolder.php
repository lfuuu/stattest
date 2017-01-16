<?php
namespace app\classes\grid\account\ott\maintenance;

use yii\db\Expression;
use yii\db\Query;
use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use app\models\billing\Clients;
use app\models\billing\Counter;

/**
 * Class AutoBlockCreditFolder
 */
class AutoBlockCreditFolder extends AccountGridFolder
{
    public $block_date;

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
            'manager',
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

        $query->addSelect('ab.block_date');
        $query->leftJoin(
            '(
                SELECT `client_id`, MAX(`date`) AS block_date
                FROM `lk_notice_log`
                WHERE `event` = "zero_balance"
                GROUP BY `client_id`
            ) AS ab',
            'ab.`client_id` = c.`id`');
        $query->andWhere(['cr.business_id' => $this->grid->getBusiness()]);
        $query->andWhere(['cr.business_process_status_id' => BusinessProcessStatus::OTT_MAINTENANCE_WORK]);
        $query->andWhere(['c.is_blocked' => 0]);

        $billingQuery
            = (new Query)
                ->select('clients.id')
                ->from(['clients' => Clients::tableName()])
                ->innerJoin(['counter' => Counter::tableName()], 'counter.client_id = clients.id')
                ->andWhere(new Expression('clients.credit < -clients.balance'));

        $clientsIDs = $billingQuery->column(Clients::getDb());
        if (count($clientsIDs)) {
            $query->andWhere(['IN', 'c.id', $clientsIDs]);
        } else {
            $query->andWhere(new Expression('false'));
        }

    }
}