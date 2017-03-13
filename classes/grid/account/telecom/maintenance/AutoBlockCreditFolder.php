<?php
namespace app\classes\grid\account\telecom\maintenance;

use app\helpers\DateTimeZoneHelper;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use app\models\billing\Clients;
use app\models\billing\Counter;
use app\models\billing\Locks;

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

        $now = new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT));

        $tz = new \DateTimeZone(Yii::$app->user->identity->timezone_name);
        $tzOffset = $tz->getOffset($now);

        $blockDateQuery = (new Query())
            ->select(new Expression('MAX(l.date) ' . ($tzOffset > 0 ? "+" : "-") . ' INTERVAL ' . abs($tzOffset) . ' SECOND'))
            ->from(['l' => ImportantEvents::tableName()])
            ->where('l.client_id = c.id')
            ->andWhere(['l.event' => ImportantEventsNames::IMPORTANT_EVENT_ZERO_BALANCE])
            ->groupBy(['l.client_id']);

        $query->addSelect(['block_date' => $blockDateQuery]);

        $query->andWhere(['cr.business_id' => $this->grid->getBusiness()]);
        $query->andWhere(['cr.business_process_status_id' => BusinessProcessStatus::TELEKOM_MAINTENANCE_WORK]);
        $query->andWhere(['c.is_blocked' => 0]);


        $clientsIDs = Clients::find()
            ->select([Clients::tableName() . '.id'])
            ->joinWith('counter', true, 'INNER JOIN')
            ->andWhere(new Expression('clients.credit < -clients.balance'))
            ->column();

        if (count($clientsIDs)) {
            $query->andWhere(['IN', 'c.id', $clientsIDs]);
        } else {
            $query->andWhere(new Expression('false'));
        }
    }
}