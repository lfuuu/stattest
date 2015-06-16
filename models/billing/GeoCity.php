<?php
namespace app\models\billing;

use app\dao\billing\GeoCityDao;
use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class GeoCity extends ActiveRecord
{
    public static function tableName()
    {
        return 'geo.city';
    }

    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

    public static function dao()
    {
        return GeoCityDao::me();
    }


}