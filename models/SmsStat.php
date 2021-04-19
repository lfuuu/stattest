<?php

namespace app\models;
use Yii;

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

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->db;
    }


    public static function getSmsByClientDate($clientId, $dateFrom, $dateTo) 
    {
        return self::find()
            ->andWhere(['sender' => $clientId])
            ->andWhere(['between', 'date_hour', $dateFrom, $dateTo])
            ->all();
    }

}