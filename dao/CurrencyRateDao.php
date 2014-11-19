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
        $rate = CurrencyRate::findOne(['currency' => $currency, 'date' => $date]);
        if ($rate === null) {
            //throw new Exception('Не найден курс вылюты ' . $currency . ' на ' . $date);
        }
        return $rate;
    }
}