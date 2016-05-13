<?php

namespace app\models\light_models;

use yii\base\Model;

class NumberPriceLight extends Model
{

    public
        $currency,
        $price;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['currency',], 'string'],
            [['price',], 'number'],
        ];
    }

    public function getFormattedPrice()
    {
        return sprintf('%.2f', $this->price);
    }

}
