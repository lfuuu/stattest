<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class UsageSms extends ActiveRecord
{
    public static function tableName()
    {
        return 'usage_sms';
    }
}