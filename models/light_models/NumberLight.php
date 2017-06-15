<?php

namespace app\models\light_models;

use app\models\ClientAccount;
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
        $number_subscriber,
        $common_ndc,
        $common_number_subscriber,
        $default_tariff;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['beauty_level', 'region', 'city_id', 'ndc_type_id', 'country_prefix', 'ndc', 'number_subscriber', 'common_ndc', 'common_number_subscriber'], 'integer'],
            [['number', 'currency', 'origin_currency'], 'string'],
            [['price', 'origin_price'], 'number'],
        ];
    }

    /**
     * @param \app\models\Number $number
     * @param string $currency
     * @param ClientAccount $clientAccount
     */
    public function setPrices(\app\models\Number $number, $currency = Currency::RUB, $clientAccount = null)
    {
        $actualPriceWithCurrency = $number->getPriceWithCurrency($currency, $clientAccount);
        $originPriceWithCurrency = $number->getOriginPriceWithCurrency($clientAccount);

        $this->price = (float)$actualPriceWithCurrency->formattedPrice;
        $this->currency = $actualPriceWithCurrency->currency;
        $this->origin_price = (float)$originPriceWithCurrency->formattedPrice;
        $this->origin_currency = $originPriceWithCurrency->currency;
    }

    /**
     * Установка полей общепринятого формата номера
     *
     * @param \app\models\Number $number
     */
    public function setCommon(\app\models\Number $number)
    {
        $this->common_number_subscriber = substr($this->number, -$number->city->postfix_length);
        $this->common_ndc = substr($this->number, strlen($this->country_prefix),
            strlen($this->number) - strlen($this->country_prefix) - $number->city->postfix_length);
    }
}