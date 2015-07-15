<?php
namespace app\models\billing;

use Yii;
use yii\db\ActiveRecord;
use app\dao\billing\GeoDao;

/**
 * @property int $id
 * @property
 */
class Geo extends ActiveRecord
{

    public static function tableName()
    {
        return 'geo.geo';
    }

    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

    public static function dao()
    {
        return GeoDao::me();
    }

}