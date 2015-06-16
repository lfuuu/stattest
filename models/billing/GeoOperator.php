<?php
namespace app\models\billing;

use app\dao\billing\GeoOperatorDao;
use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class GeoOperator extends ActiveRecord
{
    public static function tableName()
    {
        return 'geo.operator';
    }

    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

    public static function dao()
    {
        return GeoOperatorDao::me();
    }


}