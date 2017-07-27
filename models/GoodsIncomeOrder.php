<?php

namespace app\models;

use app\classes\model\ActiveRecord;

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
