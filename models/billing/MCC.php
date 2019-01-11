<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use Yii;

/**
 * @property string $mcc
 * @property string $country
 * @property string $iso
 * @property string $country_code
 */
class MCC extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.mcc';
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
