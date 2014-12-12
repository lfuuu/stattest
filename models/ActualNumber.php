<?php
namespace app\models;

use app\dao\ActualNumberDao;
use yii\db\ActiveRecord;

class ActualNumber extends ActiveRecord
{
    public static function tableName()
    {
        return 'actual_number';
    }

    public static function dao()
    {
        return ActualNumberDao::me();
    }
}
