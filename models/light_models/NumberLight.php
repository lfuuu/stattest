<?php

namespace app\models\light_models;

use app\models\ClientAccount;
use app\models\Currency;
use app\models\Number;
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
        $price2,
        $region,
        $city_id,
        $ndc_type_id,
        $country_prefix,
        $ndc,
        $number_subscriber,
        $common_ndc,
        $common_number_subscriber,
        $did_group_id,
        $source,
        $default_tariff,
        $calls_per_month = [];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['beauty_level', 'region', 'city_id', 'ndc_type_id', 'country_prefix', 'ndc', 'number_subscriber', 'common_ndc', 'common_number_subscriber', 'did_group_id'], 'integer'],
            [['number', 'currency', 'origin_currency', 'source'], 'string'],
            [['price', 'price2', 'origin_price'], 'number'],
        ];
    }

    /**
     * @param \app\models\Number $number
     * @param string $currency
     * @param ClientAccount $clientAccount
     * @throws \Exception
     */
    public function setPrices(\app\models\Number $number, $currency = Currency::RUB, $clientAccount = null)
    {
        $actualPriceWithCurrency = $number->getPriceWithCurrency($currency, $clientAccount);
        $originPriceWithCurrency = $number->getOriginPriceWithCurrency($clientAccount);

        $this->price = $actualPriceWithCurrency->formattedPrice;
        $this->currency = $actualPriceWithCurrency->currency;
        $this->origin_price = $originPriceWithCurrency->formattedPrice;
        $this->origin_currency = $originPriceWithCurrency->currency;

        if (!$clientAccount) {
            $this->price2 = $number->getOriginPrice(null, ClientAccount::PRICE_LEVEL2);
        }
    }

    public function setCallsStatistic(\app\models\Number $number)
    {
        static $monthNumbers = [];

        if (!$monthNumbers) {
            $date = new \DateTimeImmutable('now');
            $monthNumbers[] = $date->format('m');

            $date = $date->modify('previous month');
            $monthNumbers[] = $date->format('m');

            $date = $date->modify('previous month');
            $monthNumbers[] = $date->format('m');
        }

        $this->calls_per_month = array_combine($monthNumbers, [
            $number->calls_per_month_0,
            $number->calls_per_month_1,
            $number->calls_per_month_2,
        ]);
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