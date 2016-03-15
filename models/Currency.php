<?php
namespace app\models;

use app\dao\CurrencyDao;
use yii\db\ActiveRecord;

/**
 * @property string id
 * @property string name
 * @property string symbol
 */
class Currency extends ActiveRecord
{
    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    const RUB = 'RUB';
    const USD = 'USD';
    const HUF = 'HUF';
    const EUR = 'EUR';

    private static $symbols = [
        self::RUB => 'руб.',
        self::USD => '$',
        self::HUF => 'HUF',
        self::EUR => 'EUR',
    ];

    private static $currencyByCountry = [
        Country::RUSSIA => self::RUB,
        Country::HUNGARY => self::HUF,
        Country::GERMANY => self::EUR,
    ];

    /**
     * Вернуть имена полей
     * @return [] [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Сокращение',
            'name' => 'Название',
            'symbol' => 'Символ',
        ];
    }

    public static function tableName()
    {
        return 'currency';
    }

    public static function dao()
    {
        return CurrencyDao::me();
    }

    public static function symbol($currencyId)
    {
        return
            isset(self::$symbols[$currencyId])
                ? self::$symbols[$currencyId]
                : $currencyId;
    }

    public static function enum()
    {
        return array_keys(self::$symbols);
    }

    public static function map()
    {
        return self::$symbols;
    }

    public static function defaultCurrencyByCountryId($countyId)
    {
        return isset(self::$currencyByCountry[$countyId]) ? self::$currencyByCountry[$countyId] : self::RUB;
    }
}