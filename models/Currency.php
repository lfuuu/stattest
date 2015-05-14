<?php
namespace app\models;

use yii\db\ActiveRecord;

class Currency extends ActiveRecord
{
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

    public static function tableName()
    {
        return 'currency';
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
}