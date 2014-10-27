<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class UsageVoip extends ActiveRecord
{
    public static function tableName()
    {
        return 'usage_voip';
    }
}