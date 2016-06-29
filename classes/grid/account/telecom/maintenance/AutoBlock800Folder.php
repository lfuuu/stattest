<?php
namespace app\classes\grid\account\telecom\maintenance;

use app\models\UsageVoip;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use app\models\billing\Clients;
use app\models\billing\Counter;
use app\models\billing\Locks;

class AutoBlock800Folder extends AccountGridFolder
{
    public $block_date;

    public function getName()
    {
        return '800-Блок';
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
                WHERE `event` = "zero_balance"
                GROUP BY `client_id`
            ) AS ab',
            'ab.`client_id` = c.`id`');
        $query->leftJoin(['uv' => UsageVoip::tableName()], 'uv.client = c.client AND CAST(NOW() AS DATE) BETWEEN uv.actual_from AND uv.actual_to');
        $query->andWhere(['cr.business_id' => $this->grid->getBusiness()]);
        $query->andWhere(['cr.business_process_status_id' => BusinessProcessStatus::TELEKOM_MAINTENANCE_WORK]);
        $query->andWhere(['c.is_blocked' => 0]);
        $query->andWhere('uv.line7800_id');

        $billingQuery =
            (new Query)
                ->select('clients.id')
                ->from(['clients' => Clients::tableName()])
                ->innerJoin(['counter' => Counter::tableName()], 'counter.client_id = clients.id')
                ->leftJoin(['lock' => Locks::tableName()], 'lock.client_id = clients.id')
                ->where(new Expression('TRUE IN (lock.voip_auto_disabled, lock.voip_auto_disabled_local)'));

        $clientsIDs = $billingQuery->column(Clients::getDb());
        if (count($clientsIDs)) {
            $query->andWhere(['IN', 'c.id', $clientsIDs]);
        }
        else {
            $query->andWhere('false');
        }

    }
}