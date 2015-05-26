<?php
namespace app\models;

use yii\db\ActiveRecord;

class EventQueue extends ActiveRecord
{
    public static function tableName()
    {
        return 'event_queue';
    }
}