<?php
namespace app\models\billing;

use app\dao\billing\TrunkDao;
use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class Trunk extends ActiveRecord
{
    public static function tableName()
    {
        return 'auth.trunk';
    }

    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

    public static function dao()
    {
        return TrunkDao::me();
    }

}