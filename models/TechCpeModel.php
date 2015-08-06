<?php
namespace app\models;

use yii\db\ActiveRecord;

class TechCpeModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'tech_cpe_models';
    }
}