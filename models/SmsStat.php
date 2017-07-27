<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * Статистика по использованию юзером ресурсов СМС
 *
 * @property int $pk
 * @property int $sender client_account_id
 * @property int $count
 * @property string $date_hour datetime
 */
class SmsStat extends ActiveRecord
{
    public static function tableName()
    {
        return 'sms_stat';
    }
}