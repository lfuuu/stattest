<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use app\dao\billing\GeoRegionDao;
use Yii;

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
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgSlave;
    }

}