<?php

namespace app\models;

use app\models\important_events\ImportantEventsNames;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Class LkClientSetting
 * @package app\models
 *
 * @property int $client_id
 * @property string $created
 * @property float $min_balance
 * @property string $min_balance_sent
 * @property int $is_min_balance_sent
 * @property float $min_day_limit
 * @property string $min_day_limit_sent
 * @property int $is_min_day_limit_sent
 * @property string $zero_balance_sent
 * @property int $is_zero_balance_sent
 * @property string $day_limit_sent
 * @property int $is_day_limit_sent

 */
class LkClientSettings extends ActiveRecord
{
    const DEFAULT_MIN_BALANCE = 1000;
    const DEFAULT_MIN_DAY_LIMIT = 200;

    public static function tableName()
    {
        return 'lk_client_settings';
    }

    public static function saveState(ClientAccount &$client, $event, $isSet = false)
    {
        if ($isSet) {
            $row = $client->lkClientSettings;
            if (!$row) {
                $row = new LkClientSettings;
                $row->client_id = $client->id;
                $row->{ImportantEventsNames::MIN_BALANCE} = self::DEFAULT_MIN_BALANCE;
                $row->{ImportantEventsNames::MIN_DAY_LIMIT} = self::DEFAULT_MIN_DAY_LIMIT;
            }
            $row->{$event . '_sent'} = new Expression('NOW()');
            $row->{'is_' . $event . '_sent'} = 1;
            $row->save();
            $client->refresh();

        } else {
            $row = $client->lkClientSettings;
            if ($row) {
                $row->{'is_' . $event . '_sent'} = 0;
                $row->save();
            }
        }

    }
}
