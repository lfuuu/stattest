<?php
namespace app\classes\grid\account\telecom\maintenance;

use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use Yii;
use yii\db\Query;


class AutoBlockFolder extends AccountGridFolder
{
    public $block_date;

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
            'currency',
            'block_date',
            'manager',
            'region',
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
        $query->andWhere(['cr.business_id' => $this->grid->getBusiness()]);
        $query->andWhere([
            'not in',
            'cr.business_process_status_id',
            [
                BusinessProcessStatus::TELEKOM_MAINTENANCE_CONNECTED,
                BusinessProcessStatus::TELEKOM_MAINTENANCE_DISCONNECTED_DEBT,
            ],
        ]);
        $query->andWhere(['c.is_blocked' => 0]);

        $pg_query = new Query();
        $pg_query->select('client_id')->from('billing.locks')->where('true IN (voip_auto_disabled, voip_auto_disabled_local)');
        $ids = $pg_query->column(\Yii::$app->dbPg);
        if (!empty($ids)) {
            $query->andWhere(['in', 'c.id', $ids]);
        }
    }
}