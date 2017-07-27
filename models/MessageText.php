<?php

namespace app\models;

use app\classes\model\ActiveRecord;

class MessageText extends ActiveRecord
{
    public static function tableName()
    {
        return 'message_text';
    }
}