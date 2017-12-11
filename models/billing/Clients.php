<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use Yii;

class Clients extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'billing.clients';
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCounter()
    {
        return $this->hasOne(CachedCounter::className(), ['client_id' => 'id']);
    }
}