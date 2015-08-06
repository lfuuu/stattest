<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class Metro extends ActiveRecord
{
    public static function tableName()
    {
        return 'metro';
    }

    public static function getList()
    {
        $arr = self::find()->all();
        return ArrayHelper::map($arr, 'id', 'name');
    }
}