<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use yii\helpers\Url;

/**
 */
class GoodsIncomeOrder extends ActiveRecord
{
    public static function tableName()
    {
        return 'g_income_order';
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return Url::to([
            '/',
            'module' => 'incomegoods',
            'action' => 'order_view',
            'id' => $this->id
        ]);
    }
}
