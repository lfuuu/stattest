<?php
namespace app\models\billing;

use Yii;
use yii\db\ActiveRecord;

class ServiceTrunk extends ActiveRecord
{

    public static function tableName()
    {
        return 'billing.service_trunk';
    }

    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

}