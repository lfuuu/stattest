<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use Yii;

class ServiceNumber extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'billing.service_number';
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