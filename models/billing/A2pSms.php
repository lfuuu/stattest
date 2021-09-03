<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use app\dao\billing\A2pSmsDao;
use app\models\billing\api\ApiMethod;
use app\dao\billing\ApiRawDao;
use Yii;

/**
 * @property int $id
 * @property boolean $orig
 * @property int $server_id
 * @property int $sms_call_id
 * @property int $account_id
 * @property string $charge_time
 * @property string $src_number
 * @property string $dst_number
 * @property string $src_route
 * @property string $dst_route
 * @property double $cost
 * @property double $count
 * @property int $account_tariff_light_id
 * @property int $package_pricelist_id
 * @property int $pricelist_location_id
 * @property int $cdr_id
 * @property int $location_id
 * @property string $mcc
 * @property string $mnc
 */
class A2pSms extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'a2p_sms_raw.a2p_sms_raw';
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

    /**
     * @return A2pSmsDao
     * @throws \yii\base\Exception
     */
    public static function dao()
    {
        return A2pSmsDao::me();
    }
}