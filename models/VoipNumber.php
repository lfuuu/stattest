<?php
namespace app\models;

use yii\db\ActiveRecord;

class VoipNumber extends ActiveRecord
{
    public static function tableName()
    {
        return 'voip_numbers';
    }
}
