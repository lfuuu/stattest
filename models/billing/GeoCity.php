<?php
namespace app\models\billing;

use app\dao\billing\GeoCityDao;
use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 */
class GeoCity extends ActiveRecord
{
    public static function tableName()
    {
        return 'geo.city';
    }

    public static function getDb()
    {
        return Yii::$app->dbPgSlave;
    }

    public static function dao()
    {
        return GeoCityDao::me();
    }


}