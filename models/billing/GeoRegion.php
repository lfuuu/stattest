<?php
namespace app\models\billing;

use Yii;
use yii\db\ActiveRecord;
use app\dao\billing\GeoRegionDao;

/**
 * @property int $id
 * @property string $name
 * @property int $zone
 * @property int $operator_region
 */
class GeoRegion extends ActiveRecord
{
    use \app\classes\traits\GetListTrait;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'geo.region';
    }

    /**
     * @return array
     */
    public static function getDb()
    {
        return Yii::$app->dbPgSlave;
    }

}