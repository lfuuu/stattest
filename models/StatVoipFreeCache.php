<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property
 */
class StatVoipFreeCache extends ActiveRecord
{
    public static function tableName()
    {
        return 'stat_voip_free_cache';
    }
}
