<?php
namespace app\models;

use yii\db\ActiveRecord;

class Language extends ActiveRecord
{

    public static function tableName()
    {
        return 'language';
    }

}
