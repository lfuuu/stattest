<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class Language extends ActiveRecord
{

    public static function tableName()
    {
        return 'language';
    }

    public static function getList()
    {
        return ArrayHelper::map(self::find()->orderBy('code desc')->all(), 'code', 'name');
    }
}
