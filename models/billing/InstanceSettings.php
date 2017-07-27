<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use Yii;


class InstanceSettings extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'billing.instance_settings';
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