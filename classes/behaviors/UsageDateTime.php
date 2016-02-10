<?php
namespace app\classes\behaviors;

use DateTime;
use DateTimeZone;
use app\classes\Assert;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use app\models\ClientAccount;

class UsageDateTime extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'setActualDateTime',
        ];
    }

    public function setActualDateTime($event)
    {
        $client = is_numeric($event->sender->client) ? ClientAccount::findOne($event->sender->client) : ClientAccount::findOne(['client' => $event->sender->client]);
        Assert::isObject($client);

        $event->sender->activation_dt = (new DateTime($event->sender->actual_from, new DateTimeZone($client->timezone_name)))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
        $event->sender->expire_dt = (new DateTime($event->sender->actual_to, new DateTimeZone($client->timezone_name)))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    }
}