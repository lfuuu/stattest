<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use NumberFormatter;
use Yii;
use yii\db\Expression;

/**
 * @property string $id
 * @property string $name
 * @property string $symbol
 * @property integer $code
 */
class Currency extends ActiveRecord
{
    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const RUB = 'RUB';
    const USD = 'USD';
    const HUF = 'HUF';
    const EUR = 'EUR';

    const CAD = 'CAD';
    const GBP = 'GBP';
    const KZT = 'KZT';
    const RSD = 'RSD';

    private static $_symbols = [];

    private static $_currencyByCountry = [
        Country::RUSSIA => self::RUB,
        Country::HUNGARY => self::HUF,
        Country::GERMANY => self::EUR,
    ];

    /**
     * Вернуть имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Сокращение',
            'name' => 'Название',
            'symbol' => 'Символ',
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'currency';
    }

    /**
     * @param string $currencyId
     * @return string
     */
    public static function symbol($currencyId)
    {
        if (!self::$_symbols) {
            self::_load();
        }
        return isset(self::$_symbols[$currencyId]) ?
            explode(' - ', self::$_symbols[$currencyId])[0] :
            $currencyId;
    }

    /**
     * @return string[]
     */
    public static function enum()
    {
        if (!self::$_symbols) {
            self::_load();
        }
        return array_keys(self::$_symbols);
    }

    /**
     * @return string[]
     */
    public static function map()
    {
        if (!self::$_symbols) {
            self::_load();
        }
        return self::$_symbols;
    }

    private static function _load()
    {
        self::$_symbols = self::find()
            ->select(new Expression("concat(symbol, ' - ', name)"))
            ->orderBy([
                "if(id='RUB', -10, if(id='HUF', -9 , if (id='EUR', -8, if(id='USD', -7,  char_length(symbol)))))" => SORT_ASC,
                'symbol' => SORT_ASC,
            ])
            ->indexBy('id')->column();
    }

    /**
     * @param int $countyId
     * @return string
     */
    public static function defaultCurrencyByCountryId($countyId)
    {
        return isset(self::$_currencyByCountry[$countyId]) ?
            self::$_currencyByCountry[$countyId] :
            self::RUB;
    }

    /**
     * Преобразовать объект в строку
     *
     * @return string
     */
    public function __toString()
    {
        return $this->id;
    }

    /**
     * Вывести валюту с учетом региональных правил форматирования
     *
     * @param float $value
     * @return string
     */
    public function format($value)
    {
        return self::formatCurrency($value, $this->id);
    }

    /**
     * Вывести валюту с учетом региональных правил форматирования
     *
     * @param float $value
     * @param string $currency
     * @return string
     */
    public static function formatCurrency($value, $currency = self::RUB)
    {
        $fmt = new NumberFormatter(Yii::$app->language, NumberFormatter::CURRENCY);
        return $fmt->formatCurrency($value, $currency);
    }

    /**
     * Получение кода валюты по Id валюты
     *
     * @param string $id
     * @return bool|string
     */
    public static function getCodeById($id)
    {
        $currencyCode = self::find()
            ->where(['id' => $id])
            ->select('code')
            ->scalar();

        if (!$currencyCode) {
            $currencyCode = self::getCodeById(self::RUB);
        }

        return $currencyCode;
    }

    /**
     * Получение ID валюты по коду
     *
     * @param string $code
     * @return bool|string
     */
    public static function getIdByCode($code)
    {
        $currencyId = self::find()
            ->where(['code' => $code])
            ->select('id')
            ->scalar();

        if (!$currencyId) {
            throw new \LogicException('Currency code not found');
        }

        return $currencyId;
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @return string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy = 'id',
            $select = 'id',
            $orderBy = ['id' => SORT_ASC],
            $where = []
        );
    }
}