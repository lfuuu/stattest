<?php
namespace app\dao;

use app\classes\Assert;
use app\classes\Singleton;
use app\helpers\DateTimeZoneHelper;
use app\models\Currency;
use app\models\CurrencyRate;
use app\queries\CurrencyRateQuery;

/**
 * @method static CurrencyRateDao me($args = null)
 */
class CurrencyRateDao extends Singleton
{

    private static $_rates = [];

    /**
     * @param string $currency
     * @param string $date
     * @return float
     */
    public static function getRate($currency, $date = '')
    {
        if ($currency === Currency::RUB) {
            return 1;
        }

        $currencyQuery = CurrencyRate::find()
            ->currency($currency)
            ->onDate($date);

        $currencyRate = $currencyQuery->one();
        Assert::isObject($currencyRate);

        return $currencyRate->getAttribute('rate');
    }

    /**
     * @param string $fromCurrencyId
     * @param string $toCurrencyId
     * @param string|\DateTime $date
     * @return float
     */
    public static function crossRate($fromCurrencyId, $toCurrencyId, $date = null)
    {
        $dateString = '';
        if ($date instanceof \DateTime) {
            $dateString = $date->format(DateTimeZoneHelper::DATE_FORMAT);
        } elseif (is_string($date) && !empty($date)) {
            $dateString = $date;
        }

        $fromCurrencyCacheKey = $fromCurrencyId . $dateString;
        $toCurrencyCacheKey = $toCurrencyId . $dateString;

        if (!array_key_exists($fromCurrencyCacheKey, self::$_rates)) {
            self::$_rates[$fromCurrencyCacheKey] = CurrencyRateDao::getRate($fromCurrencyId, $date);
        }

        if (!array_key_exists($toCurrencyCacheKey, self::$_rates)) {
            self::$_rates[$toCurrencyCacheKey] = CurrencyRateDao::getRate($toCurrencyId, $date);
        }

        if (!self::$_rates[$fromCurrencyCacheKey]) {
            \Yii::error(
                'Unknown currency rate for "' . $fromCurrencyId . '"' .
                (!is_null($date) ? 'on date "' . $dateString . '"' : '')
            );
            return null;
        }

        if (!self::$_rates[$toCurrencyCacheKey]) {
            \Yii::error(
                'Unknown currency rate for "' . $toCurrencyId . '"' .
                (!is_null($date) ? 'on date "' . $dateString . '"' : '')
            );
            return null;
        }

        return self::$_rates[$fromCurrencyCacheKey] / self::$_rates[$toCurrencyCacheKey];
    }

}