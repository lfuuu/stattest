<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use Yii;

/**
 * @property string $mnc
 * @property string $network
 * @property string $mcc
 */
class MNC extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.mnc';
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
     * Связка со страной
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMccModel()
    {
        return $this->hasOne(MCC::className(), ['mcc' => 'mcc']);
    }
}
