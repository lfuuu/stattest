<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class LogUsageHistory
 *
 * @property integer $id
 * @property string $service
 * @property integer $service_id
 * @property integer $user_id
 * @property string $ts
 * @property LogUsageHistoryFields[] $fields
 */
class LogUsageHistory extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'log_usage_history';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFields()
    {
        return $this->hasMany(LogUsageHistoryFields::className(), ['log_usage_history_id' => 'id']);
    }

}