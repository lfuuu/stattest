<?php

namespace app\models;

use app\classes\model\ActiveRecord;

class Domain extends ActiveRecord
{
    public static function tableName()
    {
        return 'domains';
    }
}
