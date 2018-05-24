<?php

namespace app\modules\callTracking\models;

use app\classes\model\ActiveRecord;
use Yii;

/**
 * @property int $id
 * @property int $account_tariff_id
 * @property int $voip_number
 * @property string $start_dt
 * @property string $disconnect_dt
 * @property string $stop_dt
 * @property string $user_agent
 * @property string $ip
 * @property string $url
 * @property string $referrer
 */
class Log extends ActiveRecord
{
    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'account_tariff_id' => 'Услуга',
            'voip_number' => 'Телефонный номер',
            'start_dt' => 'Время начала аренды номера',
            'disconnect_dt' => 'Время разрыва коннекта с юзер-агентом',
            'stop_dt' => 'Время окончания аренды номера',
            'user_agent' => 'User agent',
            'ip' => 'IP',
            'url' => 'URL',
            'referrer' => 'Referrer',
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'call_tracking.log';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgCallTracking;
    }
}