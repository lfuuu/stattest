<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\queries\UsageVoipQuery;

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

    public static function find()
    {
        return new UsageVoipQuery(get_called_class());
    }
}

