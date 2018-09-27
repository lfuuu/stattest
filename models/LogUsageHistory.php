<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * Class LogUsageHistory
 *
 * @property integer $id
 * @property string $service
 * @property integer $service_id
 * @property integer $user_id
 * @property string $ts
 * @property-read LogUsageHistoryFields[] $fields
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
        return $this->hasMany(LogUsageHistoryFields::class, ['log_usage_history_id' => 'id']);
    }

}