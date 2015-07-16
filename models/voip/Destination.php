<?php
namespace app\models\voip;

use yii\db\ActiveRecord;

class Destination extends ActiveRecord
{

    public static function tableName()
    {
        return 'voip_destination';
    }

    public function getDestinationPrefixes()
    {
        return $this->hasMany(DestinationPrefixes::className(), ['destination_id' => 'id']);
    }

    public function getPrefixes()
    {
        return $this->hasMany(Prefixlist::className(), ['id' => 'prefix_id'])->via('destinationPrefixes');
    }

}