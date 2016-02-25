<?php
namespace app\classes\behaviors;

use DateTime;
use DateTimeZone;
use app\classes\Assert;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;

class UsageDateTime extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'setActualDateTime',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'setActualDateTime',
        ];
    }

    public function setActualDateTime($event)
    {
        $timezone = DateTimeZoneHelper::TIMEZONE_MOSCOW;

        if (isset($event->sender->client)) {
            $client = is_numeric($event->sender->client) ? ClientAccount::findOne($event->sender->client) : ClientAccount::findOne(['client' => $event->sender->client]);
            Assert::isObject($client);

            $timezone = $client->timezone_name;
        }

        $event->sender->activation_dt =
            (new DateTime($event->sender->actual_from, new DateTimeZone($timezone)))
                ->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT))
                ->format(DateTime::ATOM);

        $event->sender->expire_dt =
            (new DateTime($event->sender->actual_to, new DateTimeZone($timezone)))
                ->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT))
                ->modify('+1 day -1 second')
                ->format(DateTime::ATOM);
    }
}