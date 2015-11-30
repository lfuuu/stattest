<?php
namespace app\models\billing;

use Yii;
use app\dao\billing\CallsDao;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class Calls extends ActiveRecord
{
    public static function tableName()
    {
        return 'calls_raw.calls_raw';
    }

    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

    public static function dao()
    {
        return CallsDao::me();
    }

}
