<?php
namespace app\models;

use yii\db\ActiveRecord;

class TechPort extends ActiveRecord
{
    public static function tableName()
    {
        return 'tech_ports';
    }
}