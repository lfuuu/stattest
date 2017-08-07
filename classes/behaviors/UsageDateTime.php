<?php
namespace app\classes\behaviors;

use app\classes\Assert;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use DateTime;
use DateTimeZone;
use yii\base\Behavior;
use yii\db\ActiveRecord;

class UsageDateTime extends Behavior
{

    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'setActualDateTime',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'setActualDateTime'
        ];
    }

    /**
     * @param \yii\base\ModelEvent $event
     * @throws \yii\base\Exception
     */
    public function setActualDateTime($event)
    {
        $timezone = DateTimeZoneHelper::TIMEZONE_MOSCOW;

        if (isset($event->sender->client)) {
            /** @var ClientAccount $clientAccount */
            $clientAccount =
                is_numeric($event->sender->client)
                    ? ClientAccount::findOne($event->sender->client)
                    : ClientAccount::findOne(['client' => $event->sender->client]);
            Assert::isObject($clientAccount, 'Missing ClientAccount #' . $event->sender->client);

            $timezone = $clientAccount->timezone_name;
        }

        $event->sender->activation_dt =
            (new DateTime($event->sender->actual_from, new DateTimeZone($timezone)))
                ->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT))
                ->format(DateTimeZoneHelper::DATETIME_FORMAT);

        $event->sender->expire_dt =
            (new DateTime($event->sender->actual_to, new DateTimeZone($timezone)))
                ->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT))
                ->modify('+1 day -1 second')
                ->format(DateTimeZoneHelper::DATETIME_FORMAT);
    }

}
