<?php

namespace app\models\billing;

use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property $dt
 * @property int $client_id
 * @property boolean $anti_fraud_disabled
 * @property boolean $is_blocked
 * @property boolean $voip_disabled
 */
class LocksLog extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'billing.locks_log';
    }

    /**
     * @return mixed
     */
    public static function getDb()
    {
        return Yii::$app->dbPgSlave;
    }

}