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

    public static function primaryKey()
    {
        return ['code'];
    }

    public static function dao()
    {
        return CountryDao::me();
    }

    public static function getList()
    {
        $arr = self::find()->where(['in_use' => 1])->orderBy('code DESC')->all();
        return ArrayHelper::map($arr, 'code', 'name');
    }

}