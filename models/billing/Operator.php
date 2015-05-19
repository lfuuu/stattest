<?php
namespace app\models\billing;

use app\dao\billing\OperatorDao;
use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class Operator extends ActiveRecord
{
    public static function tableName()
    {
        return 'voip.operator';
    }

    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

    public static function dao()
    {
        return OperatorDao::me();
    }


}