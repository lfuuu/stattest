<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class Bank extends ActiveRecord
{
    public static function tableName()
    {
        return 'bik';
    }

    public static function getList()
    {
        $arr = self::find()->all();
        $res = [];
        foreach ($arr as $row) {
            $res[$row['bik']] = $row['bik'] . '(' . $row['bank_name'] . ')';
        }
        return $res;
    }
}