<?php
namespace app\models\billing;

use Yii;
use yii\db\ActiveRecord;

class ServiceNumber extends ActiveRecord
{

    public static function tableName()
    {
        return 'billing.service_number';
    }

    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

}