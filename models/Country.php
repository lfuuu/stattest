<?php
namespace app\models;

use app\dao\CountryDao;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property string $enabled
 * @property
 */
class Country extends ActiveRecord
{
    const RUSSIA = 276;
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
}