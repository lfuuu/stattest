<?php

namespace app\models\light_models;

use app\models\Currency;
use yii\base\Model;

/**
 * Class NumberLight
 */
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
        $ndc_type_id,
        $country_prefix,
        $ndc,
        $number_subscriber;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['beauty_level', 'region', 'city_id', 'ndc_type_id', 'country_prefix', 'ndc', 'number_subscriber'], 'integer'],
            [['number', 'currency', 'origin_currency'], 'string'],
            [['price', 'origin_price'], 'number'],
        ];
    }

    /**
     * @param \app\models\Number $number
     * @param string $currency
     */
    public function setPrices(\app\models\Number $number, $currency = Currency::RUB)
    {
        $actualPriceWithCurrency = $number->getPriceWithCurrency($currency);
        $originPriceWithCurrency = $number->getOriginPriceWithCurrency();

        $this->price = (float)$actualPriceWithCurrency->formattedPrice;
        $this->currency = $actualPriceWithCurrency->currency;
        $this->origin_price = (float)$originPriceWithCurrency->formattedPrice;
        $this->origin_currency = $originPriceWithCurrency->currency;
    }
}