<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class LogBlock extends ActiveRecord
{
    public static function tableName()
    {
        return 'log_block';
    }
}