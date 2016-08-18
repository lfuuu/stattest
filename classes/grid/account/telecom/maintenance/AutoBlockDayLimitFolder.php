<?php
namespace app\classes\grid\account\telecom\maintenance;

use Yii;
use yii\db\Expression;
use yii\db\Query;
use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use app\models\billing\Clients;
use app\models\billing\Counter;
use app\models\billing\Locks;

class AutoBlockDayLimitFolder extends AccountGridFolder
{
    public $block_date;

    public function getName()
    {
        return 'Сут. Блок';
    }

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

    public function queryParams(Query $query)
    {
        parent::queryParams($query);

        $query->addSelect('ab.block_date');
        $query->leftJoin(
            '(
                SELECT `client_id`, MAX(`date`) AS block_date
                FROM `lk_notice_log`
                WHERE `event` = "day_limit"
                GROUP BY `client_id`
            ) AS ab',
            'ab.`client_id` = c.`id`');
        $query->andWhere(['cr.business_id' => $this->grid->getBusiness()]);
        $query->andWhere(['cr.business_process_status_id' => BusinessProcessStatus::TELEKOM_MAINTENANCE_WORK]);
        $query->andWhere(['c.voip_disabled' => 1]);

        $billingQuery =
            (new Query)
                ->select('clients.id')
                ->from(['clients' => Clients::tableName()])
                ->innerJoin(['counter' => Counter::tableName()], 'counter.client_id = clients.id')
                ->andWhere(new Expression('clients.voip_limit_day > 0'))
                ->andWhere(new Expression('clients.voip_limit_day < counter.amount_day_sum'));

        $clientsIDs = $billingQuery->column(Clients::getDb());

        if (count($clientsIDs)) {
            $query->andWhere(['IN', 'c.id', $clientsIDs]);
        }
        else {
            $query->andWhere('false');
        }

    }
}