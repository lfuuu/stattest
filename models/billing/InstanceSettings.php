<?php
namespace app\models\billing;

use Yii;
use yii\db\ActiveRecord;


class InstanceSettings extends ActiveRecord
{

    public static function tableName()
    {
        return 'billing.instance_settings';
    }

    public static function getDb()
    {
        return Yii::$app->dbPgSlave;
    }

}