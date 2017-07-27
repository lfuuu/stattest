<?php

namespace app\models\voip;

use app\classes\model\ActiveRecord;

class Destination extends ActiveRecord
{
    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'voip_destination';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDestinationPrefixes()
    {
        return $this->hasMany(DestinationPrefixes::className(), ['destination_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPrefixes()
    {
        return $this->hasMany(Prefixlist::className(), ['id' => 'prefixlist_id'])->via('destinationPrefixes');
    }

}