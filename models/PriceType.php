<?php
namespace app\models;

use yii\db\ActiveRecord;

class PriceType extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'g_price_type';
    }
}