<?php
namespace app\models;

use yii\db\ActiveRecord;

class GoodPriceType extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait;

    const RETAIL = '739a53ba-8389-11df-9af5-001517456eb1';

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'g_price_type';
    }
}