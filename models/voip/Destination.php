<?php
namespace app\models\voip;

use yii\db\ActiveRecord;
use app\dao\VoipDestinationDao;

class Destination extends ActiveRecord
{

    public static function tableName()
    {
        return 'voip_destination';
    }

    public static function dao()
    {
        return VoipDestinationDao::me();
    }

    public function getDestinationPrefixes()
    {
        return $this->hasMany(DestinationPrefixes::className(), ['destination_id' => 'id']);
    }

    public function getPrefixes()
    {
        return $this->hasMany(Prefixlist::className(), ['id' => 'prefixlist_id'])->via('destinationPrefixes');
    }

}