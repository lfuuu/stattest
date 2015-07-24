<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class PriceType extends ActiveRecord
{
    public static function tableName()
    {
        return 'g_price_type';
    }

    public static function getList()
    {
        $arr = self::find()->orderBy(['name' => SORT_DESC])->all();
        return ArrayHelper::map($arr, 'id', 'name');
    }
}