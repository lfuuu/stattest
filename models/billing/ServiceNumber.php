<?php
namespace app\models\billing;

use Yii;
use yii\db\ActiveRecord;

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