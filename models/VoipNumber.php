<?php
namespace app\models;

use yii\db\ActiveRecord;

class VoipNumber extends ActiveRecord
{

    public static function tableName()
    {
        return 'voip_numbers';
    }

    public function getDidGroup()
    {
        return $this->hasOne(TariffNumber::className(), ['did_group_id' => 'did_group_id']);
    }

}
