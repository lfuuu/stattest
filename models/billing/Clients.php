<?php

namespace app\models\billing;

use Yii;
use yii\db\ActiveRecord;

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
     * @return mixed
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
        return $this->hasOne(Counter::className(), ['client_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLock()
    {
        return $this->hasOne(Locks::className(), ['client_id' => 'id']);
    }

}