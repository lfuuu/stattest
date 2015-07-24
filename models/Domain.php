<?php
namespace app\models;

use yii\db\ActiveRecord;

class Domain extends ActiveRecord
{
    public static function tableName()
    {
        return 'domains';
    }
}
