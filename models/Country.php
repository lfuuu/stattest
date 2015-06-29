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
        return ArrayHelper::map(self::find()->all(), 'code', 'name');
    }
}