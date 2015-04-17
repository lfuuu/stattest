<?php
namespace app\models\billing;

use app\dao\billing\NumberDao;
use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
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
        return Yii::$app->dbPg;
    }

    public static function dao()
    {
        return NumberDao::me();
    }

}