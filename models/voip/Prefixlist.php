<?php
namespace app\models\voip;

use yii\db\ActiveRecord;

class Prefixlist extends ActiveRecord
{

    const TYPE_MANUAL = 1;
    const TYPE_ROSLINK = 3;

    public static $types = [
        self::TYPE_MANUAL => 'Вручную',
        self::TYPE_ROSLINK => 'РосСвязь',
    ];

    public static function tableName()
    {
        return 'voip_prefixlist';
    }

}