<?php
namespace app\models\billing;

use Yii;
use yii\db\ActiveRecord;
use app\dao\billing\GeoCountryDao;

/**
 * @property int $id
 */
class GeoCountry extends ActiveRecord
{
    public static function tableName()
    {
        return 'geo.country';
    }

    public static function getDb()
    {
        return Yii::$app->dbPgSlave;
    }

    public static function dao()
    {
        return GeoCountryDao::me();
    }


}