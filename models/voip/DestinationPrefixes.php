<?php
namespace app\models\voip;

use yii\db\ActiveRecord;

class DestinationPrefixes extends ActiveRecord
{

    public static function tableName()
    {
        return 'voip_destination_prefixes';
    }

}