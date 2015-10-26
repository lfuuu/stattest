<?php
namespace app\models\billing;

use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class StatPackage extends ActiveRecord
{
    public static function tableName()
    {
        return 'billing.stats_package';
    }

    public static function getDb()
    {
        return Yii::$app->dbPg;
    }
}
