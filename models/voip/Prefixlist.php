<?php
namespace app\models\voip;

use yii\db\ActiveRecord;

class Prefixlist extends ActiveRecord
{

    public static function tableName()
    {
        return 'voip_prefixlist';
    }

}