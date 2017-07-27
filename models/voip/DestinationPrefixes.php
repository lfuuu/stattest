<?php

namespace app\models\voip;

use app\classes\model\ActiveRecord;

class DestinationPrefixes extends ActiveRecord
{

    public static function tableName()
    {
        return 'voip_destination_prefixes';
    }

}