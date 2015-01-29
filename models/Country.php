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
    public static function tableName()
    {
        return 'country';
    }

    public static function dao()
    {
        return CountryDao::me();
    }
}