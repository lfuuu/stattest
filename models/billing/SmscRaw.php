<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use app\dao\billing\SmscRawDao;
use Yii;

/**
 * @property int id
 * @property int server_id
 * @property int account_id
 * @property string orig_gt
 * @property string smpp_gt
 * @property string term_gt
 * @property string src_number
 * @property string dst_number
 * @property string src_imsi
 * @property string dst_imsi
 * @property int orig_cdr_id
 * @property int smpp_cdr_id
 * @property int term_cdr_id
 * @property string setup_time
 * @property int account_tariff_light_id
 * @property int package_pricelist_id
 * @property int pricelist_location_id
 * @property float cost
 * @property float rate
 * @property int count
 * @property int location_id
 * @property int service_start_timestamp
 * @property int id_since_start
 */
class SmscRaw extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'smsc_raw.smsc_raw';
    }


    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'setup_time' => 'Время подключения',
            'rate' => 'Ставка',
            'cost' => 'Стоимость',
            'count' => 'Количество',
        ];
    }

    /**
     * @return SmscRawDao
     * @throws \yii\base\Exception
     */
    public static function dao()
    {
        return SmscRawDao::me();
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
