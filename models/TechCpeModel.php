<?php

namespace app\models;

use app\classes\model\ActiveRecord;

class TechCpeModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'tech_cpe_models';
    }
}