<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class CoreSyncIds
 *
 * @property int $id
 * @property string $type
 * @property string $external_id
 */
class CoreSyncIds extends ActiveRecord
{
    const TYPE_SUPER_CLIENT = 'super_client';
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'core_sync_ids';
    }
}
