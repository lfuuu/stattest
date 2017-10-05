<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 */
class StatVoipFreeCache extends ActiveRecord
{
    public static function tableName()
    {
        return 'stat_voip_free_cache';
    }
}
