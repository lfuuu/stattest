<?php
namespace app\models;

use yii\db\ActiveRecord;

class Metro extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'metro';
    }
}