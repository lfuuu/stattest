<?php
namespace app\models;

use yii\db\ActiveRecord;

class Datacenter extends ActiveRecord
{
    public static function tableName()
    {
        return 'datacenter';
    }

    public function getRegion()
    {
        return $this->hasOne(Region::className(), ['id' => 'region']);
    }
}
