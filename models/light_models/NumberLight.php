<?php

namespace app\models\light_models;

use yii\base\Model;
use app\models\Number;
use app\models\Currency;

class NumberLight extends Model
{

    public
        $number,
        $beauty_level,
        $price,
        $currency,
        $origin_price,
        $origin_currency,
        $region,
        $city_id,
        $did_group_id,
        $number_type,
        $site_publish,
        $country_code;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['beauty_level', 'region', 'city_id', 'did_group_id', 'number_type', 'site_publish', 'country_code'], 'integer'],
            [['number', 'currency', 'origin_currency',], 'string'],
            [['price', 'origin_price'], 'number'],
        ];
    }

    /**
     * @param \app\models\Number $number
     * @param string $currency
     */
    public function setPrices(\app\models\Number $number, $currency = Currency::RUB)
    {
        $actualPrice = $number->getPriceWithCurrency($currency);
        $originPrice = $number->getOriginPriceWithCurrency();

        $this->price = (float) $actualPrice->formattedPrice;
        $this->currency = $actualPrice->currency;
        $this->origin_price = (float) $originPrice->formattedPrice;
        $this->origin_currency = $originPrice->currency;
    }
}