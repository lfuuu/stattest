<?php
namespace app\models;

use app\dao\CurrencyRateDao;
use app\queries\CurrencyRateQuery;
use yii\db\ActiveRecord;

/**
 * @property int    $id
 * @property string $date           дата yyyy-mm-dd
 * @property string $currency       валюта: USD, RUR
 * @property float  $rate           значение курса на дату
 * @property
 */
class CurrencyRate extends ActiveRecord
{
    public static function tableName()
    {
        return 'bill_currency_rate';
    }

    public static function dao()
    {
        return CurrencyRateDao::me();
    }

    public static function find()
    {
        return new CurrencyRateQuery(get_called_class());
    }
}