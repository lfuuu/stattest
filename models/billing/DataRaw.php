<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use Yii;

/**
 * @property int id
 * @property bool orig
 * @property int server_id
 * @property string charge_time
 * @property int account_id
 * @property int number_service_id
 * @property float rate
 * @property float cost
 * @property int quantity
 * @property string msisdn
 * @property string imsi
 * @property string mcc
 * @property string mnc
 * @property int account_tariff_light_id
 * @property int nnp_package_data_id
 * @property int cdr_id
 */
class DataRaw extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'data_raw.data_raw';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgSlave;
    }
}
