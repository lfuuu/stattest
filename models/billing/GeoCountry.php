<?php
namespace app\models\billing;

use Yii;
use yii\db\ActiveRecord;
use app\dao\billing\GeoCountryDao;

/**
 * @property int $id
 * @property string $name
 */
class GeoCountry extends ActiveRecord
{
    use \app\classes\traits\GetListTrait;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'geo.country';
    }

    /**
     * @return array
     */
    public static function getDb()
    {
        return Yii::$app->dbPgSlave;
    }

    public static function dao()
    {
        return GeoCountryDao::me();
    }

}