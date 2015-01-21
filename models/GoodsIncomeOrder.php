<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property
 */
class GoodsIncomeOrder extends ActiveRecord
{
    public static function tableName()
    {
        return 'g_income_order';
    }
}
