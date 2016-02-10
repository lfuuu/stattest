<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\dao\ActualNumberDao;

class ActualNumber extends ActiveRecord
{

    public function behaviors()
    {
        return [
            'ImportantEvents' => \app\classes\behaviors\important_events\ActualNumber::className(),
        ];
    }

    public static function tableName()
    {
        return 'actual_number';
    }

    public static function dao()
    {
        return ActualNumberDao::me();
    }

}
