<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\CurrencyRate;
use yii\db\Exception;

/**
 * @method static CurrencyRateDao me($args = null)
 * @property
 */
class CurrencyRateDao extends Singleton
{
    /**
     * @return CurrencyRate
     */
    public static function findRate($currency, $date)
    {
        return CurrencyRate::findOne(['currency' => $currency, 'date' => $date]);
    }

    /**
     * @return float
     */
    public static function getRate($fromCurrencyId, $toCurrencyId, \DateTime $datetime)
    {
        // TODO Сделать конвертацию кросскурсов
        $rate =
            CurrencyRate::find()
                ->andWhere(['currency' => $toCurrencyId])
                ->andWhere('date <= :date', [':date' => $datetime->format('Y-m-d')])
                ->orderBy('date desc')
                ->one();
        return $rate === null ? null : $rate->rate;
    }
}