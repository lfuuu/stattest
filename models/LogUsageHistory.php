<?php
namespace app\models;

use yii\db\ActiveRecord;

class LogUsageHistory extends ActiveRecord
{

    public static function tableName()
    {
        return 'log_usage_history';
    }

    public function getFields()
    {
        return $this->hasMany(LogUsageHistoryFields::className(), ['log_usage_history_id' => 'id']);
    }

}