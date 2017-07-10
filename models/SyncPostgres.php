<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class SyncPostgres
 */
class SyncPostgres extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'z_sync_postgres';
    }
}
