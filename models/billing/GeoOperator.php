<?php
namespace app\models\billing;

use app\dao\billing\GeoOperatorDao;
use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 */
class GeoOperator extends ActiveRecord
{
    public static function tableName()
    {
        return 'geo.operator';
    }

    public static function getDb()
    {
        return Yii::$app->dbPgSlave;
    }

    public static function dao()
    {
        return GeoOperatorDao::me();
    }


}