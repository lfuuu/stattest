<?php
namespace app\models;

use app\dao\NumberDao;
use app\models\light_models\CurrencyLight;
use app\models\light_models\NumberPriceLight;
use yii\db\ActiveRecord;

/**
 * @property string number
 * @property string status
 * @property string reserve_from
 * @property string reserve_till
 * @property string hold_from
 * @property string hold_to
 * @property int beauty_level
 * @property int price
 * @property int region
 * @property int client_id
 * @property int usage_id
 * @property string reserved_free_date
 * @property string used_until_date
 * @property int edit_user_id
 * @property string site_publish
 * @property int city_id
 * @property int did_group_id
 * @property int ndc
 * @property string number_subscriber
 * @property int number_type
 * @property string date_start
 * @property string date_end
 * @property int operator_account_id
 * @property int country_code
 *
 * @property City $city
 * @property DidGroup $didGroup
 * @property UsageVoip $usage
 * @property NumberType $numberType
 * @property TariffNumber $tariff
 * @property array $actualPrice
 */
class Number extends ActiveRecord
{

    const STATUS_INSTOCK = 'instock';
    const STATUS_HOLD = 'hold';
    const STATUS_ACTIVE = 'active';
    const STATUS_RESERVED = 'reserved';
    const STATUS_NOTSELL = 'notsell';

    public static $statusList = [
        self::STATUS_NOTSELL => 'Не продается',
        self::STATUS_INSTOCK => 'Свободен',
        self::STATUS_RESERVED => 'В резерве',
        self::STATUS_ACTIVE => 'Используется',
        self::STATUS_HOLD => 'В отстойнике',
    ];

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'voip_numbers';
    }

    /**
     * Вернуть имена полей
     * @return [] [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'number' => 'Номер',
            'client_id' => 'Клиент',
            'usage_id' => 'Услуга',
            'city_id' => 'Город',
            'did_group_id' => 'DID группа',
            'beauty_level' => 'Степень красивости',
        ];
    }

    /**
     * @return NumberDao
     */
    public static function dao()
    {
        return NumberDao::me();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDidGroup()
    {
        return $this->hasOne(DidGroup::className(), ['id' => 'did_group_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNumberType()
    {
        return $this->hasOne(NumberType::className(), ['id' => 'number_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTariff()
    {
        return $this->hasOne(TariffNumber::className(), ['city_id' => 'city_id', 'did_group_id' => 'did_group_id']);
    }

    /**
     * @return float
     */
    public function getPrice($currency = Currency::RUB)
    {
        $price = $this->originPrice;

        if ($this->tariff->currency != $currency) {
            if (($tariffCurrencyRate = CurrencyRate::find()->currency($this->tariff->currency)) !== null) {
                $price *= $tariffCurrencyRate->rate;
            }

            if (($currencyRate = CurrencyRate::find()->currency($currency)) !== null) {
                $price /= $currencyRate->rate;
            }
        }

        return $price;
    }

    /**
     * @param string $currency
     * @return NumberPriceLight
     */
    public function getPriceWithCurrency($currency = Currency::RUB)
    {
        $formattedResult = new NumberPriceLight;
        $formattedResult->setAttributes([
            'currency' => $currency,
            'price' => $this->getPrice($currency),
        ]);
        return $formattedResult;
    }

    /**
     * @return float
     */
    public function getOriginPrice()
    {
        return $this->tariff->activation_fee;
    }

    /**
     * @return NumberPriceLight
     */
    public function getOriginPriceWithCurrency()
    {
        $formattedResult = new NumberPriceLight;
        $formattedResult->setAttributes([
            'currency' => $this->tariff->currency->id,
            'price' => $this->originPrice,
        ]);
        return $formattedResult;
    }

}