<?php
namespace app\models\billing;

use Yii;
use yii\db\ActiveRecord;
use app\dao\billing\GeoRegionDao;

/**
 * @property int $id
 * @property
 */
class GeoRegion extends ActiveRecord
{

    public static function tableName()
    {
        return 'geo.region';
    }

    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

}