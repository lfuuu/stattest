<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class LogClientFields extends ActiveRecord
{
    public static function tableName()
    {
        return 'log_client_fields';
    }
}