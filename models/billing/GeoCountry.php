<?php
namespace app\models\billing;

use Yii;
use yii\db\ActiveRecord;
use app\dao\billing\GeoCountryDao;

/**
 * @property int $id
 * @property
 */
class GeoCountry extends ActiveRecord
{
    public static function tableName()
    {
        return 'geo.country';
    }

    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

    public static function dao()
    {
        return GeoCountryDao::me();
    }


}