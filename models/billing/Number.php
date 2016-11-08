<?php
namespace app\models\billing;

use app\dao\billing\NumberDao;
use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 */
class Number extends ActiveRecord
{
    const TYPE_SRC = 1;
    const TYPE_DST = 2;

    public static function tableName()
    {
        return 'auth.number';
    }

    public static function getDb()
    {
        return Yii::$app->dbPgSlave;
    }

    public static function dao()
    {
        return NumberDao::me();
    }

}