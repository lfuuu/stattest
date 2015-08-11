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

    const TYPE_ROSLINK_ALL = 'all';
    const TYPE_ROSLINK_FIXED = 'fixed';
    const TYPE_ROSLINK_MOBILE = 'mobile';

    public static $roslink_types = [
        self::TYPE_ROSLINK_ALL => 'Любые',
        self::TYPE_ROSLINK_FIXED => 'Стационарные',
        self::TYPE_ROSLINK_MOBILE => 'Мобильные',
    ];

    public static function tableName()
    {
        return 'voip_prefixlist';
    }

}