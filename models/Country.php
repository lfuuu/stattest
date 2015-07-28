<?php
namespace app\models;

use app\dao\CountryDao;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * @property int $id
 * @property string $name
 * @property string $enabled
 * @property
 */
class Country extends ActiveRecord
{
    const RUSSIA = 643;
    const HUNGARY = 348;
    const GERMANY = 276;

    public static function tableName()
    {
        return 'country';
    }

    public static function dao()
    {
        return CountryDao::me();
    }

    public static function getList()
    {
        $arr = self::findAll(['in_use' => 1]);
        return ArrayHelper::map($arr, 'code', 'name');
    }

}