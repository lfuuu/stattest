<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class UsageVirtpbx extends ActiveRecord
{
    public static function tableName()
    {
        return 'usage_virtpbx';
    }
}