<?php

namespace app\models;

use app\classes\model\ActiveRecord;

class LogUsageHistoryFields extends ActiveRecord
{

    public static function tableName()
    {
        return 'log_usage_history_fields';
    }

}


