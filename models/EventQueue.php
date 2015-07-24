<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class EventQueue extends ActiveRecord
{
    public static function tableName()
    {
        return 'event_queue';
    }
}