<?php
namespace app\models;

use yii\db\ActiveRecord;

class CoreSyncIds extends ActiveRecord
{
    public static function tableName()
    {
        return 'core_sync_ids';
    }
}
