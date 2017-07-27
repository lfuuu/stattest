<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use Yii;

/**
 * @property int $id
 */
class StatPackage extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'billing.stats_package';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPg;
    }
}
