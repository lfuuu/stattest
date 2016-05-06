<?php
namespace app\models;

use yii\db\ActiveRecord;

class MessageText extends ActiveRecord
{
    public static function tableName()
    {
        return 'message_text';
    }
}