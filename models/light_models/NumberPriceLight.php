<?php

namespace app\models\light_models;

use yii\base\Model;

/**
 * @property string $formattedPrice
 */
class NumberPriceLight extends Model
{
    /** @var string */
    public $currency;

    /** @var float */
    public $price;

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

    /**
     * @return string
     */
    public function getFormattedPrice()
    {
        return is_null($this->price) ? $this->price : (float)sprintf('%.2f', $this->price);
    }

}
