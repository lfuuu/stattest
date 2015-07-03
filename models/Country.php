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
        $arr = self::find()->all();
        return ArrayHelper::map($arr, 'code', 'name');
    }

}