<?php
namespace app\models;

use app\dao\RegionDao;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * @property int $id
 * @property string $name
 * @property string $short_name
 * @property int $code
 * @property string $timezone_name
 * @property int $country_id
 * @property
 */
class Region extends ActiveRecord
{
    const MOSCOW = 99;
    const TIMEZONE_MOSCOW = 'Europe/Moscow';

    public static function tableName()
    {
        return 'regions';
    }

    public static function dao()
    {
        return RegionDao::me();
    }

    public static function getList()
    {
        $arr = self::find()->all();
        return ArrayHelper::map($arr, 'id', 'name');
    }

    public static function getTimezoneList()
    {
        $arr = self::find()->groupBy(['timezone_name'])->all();
        return ArrayHelper::map($arr, 'timezone_name', 'timezone_name');
    }
}