<?php
namespace app\models\voip;

use yii\db\ActiveRecord;

class Destination extends ActiveRecord
{

    public static function tableName()
    {
        return 'voip_destination';
    }

}