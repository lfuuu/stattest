<?php
namespace app\models\billing;

use Yii;
use app\dao\billing\CallsAggrDao;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class CallsAggr extends ActiveRecord
{
    public static function tableName()
    {
        return 'calls_aggr.calls_aggr';
    }

    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

    public static function dao()
    {
        return CallsAggrDao::me();
    }
}
