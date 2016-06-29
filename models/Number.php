<?php
namespace app\models;

use app\dao\NumberDao;
use app\models\light_models\NumberPriceLight;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * @property string number
 * @property string status
 * @property string reserve_from
 * @property string reserve_till
 * @property string hold_from
 * @property string hold_to
 * @property int beauty_level
 * @property float price
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
 * @property int calls_per_month_0 Кол-во звонков за текущий месяц
 * @property int calls_per_month_1 Кол-во звонков за -1 месяц
 * @property int calls_per_month_2 Кол-во звонков за -2 месяц
 *
 * @property City $city
 * @property DidGroup $didGroup
 * @property UsageVoip $usage
 * @property NumberType $numberType
 * @property TariffNumber $tariff
 * @property ClientAccount $clientAccount
 *
 * @property array $actualPrice
 */
class Number extends ActiveRecord
{
    const STATUS_NOTSALE = 'notsale';
    const STATUS_INSTOCK = 'instock';
    const STATUS_ACTIVE_TESTED = 'active_tested';
    const STATUS_ACTIVE_COMMERCIAL = 'active_commercial';
    const STATUS_NOTACTIVE_RESERVED = 'notactive_reserved';
    const STATUS_NOTACTIVE_HOLD = 'notactive_hold';
    const STATUS_RELEASED = 'released';

    const STATUS_GROUP_ACTIVE = 'active';
    const STATUS_GROUP_NOTACTIVE = 'notactive';

    public static $statusList = [
        self::STATUS_NOTSALE => 'Не продается',
        self::STATUS_INSTOCK => 'Свободен',
        self::STATUS_ACTIVE_TESTED => 'Используется. Тестируется.',
        self::STATUS_ACTIVE_COMMERCIAL => 'Используется. В коммерции.',
        self::STATUS_NOTACTIVE_RESERVED => 'В резерве',
        self::STATUS_NOTACTIVE_HOLD => 'В отстойнике',
        self::STATUS_RELEASED => 'Откреплен',
    ];

    public static $statusGroup = [
        self::STATUS_GROUP_ACTIVE => [self::STATUS_ACTIVE_TESTED, self::STATUS_ACTIVE_COMMERCIAL],
        self::STATUS_GROUP_NOTACTIVE => [self::STATUS_NOTACTIVE_RESERVED, self::STATUS_NOTACTIVE_HOLD],
    ];

    protected $callsCount = null;

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
            'status' => 'Статус',
            'number_type' => 'Тип номера',
        ];
    }

    public function rules()
    {
        return [
            [['status'], 'string'],
            [['beauty_level', 'did_group_id'], 'integer'],
            [['status', 'beauty_level', 'did_group_id'], 'required', 'on' => 'save'],
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
    public function getUsage()
    {
        return $this->hasOne(UsageVoip::className(), ['id' => 'usage_id']);
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
    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['id' => 'client_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTariff()
    {
        return $this->hasOne(TariffNumber::className(), ['city_id' => 'city_id', 'did_group_id' => 'did_group_id']);
    }

    /**
     * @param null|string $currency
     * @return float
     */
    public function getPrice($currency = null)
    {
        $price = $this->originPrice;

        if (!is_null($currency) && $this->tariff->currency != $currency) {
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

    /**
     * @return string
     */
    public function getUrl()
    {
        return self::getUrlById($this->number);
    }

    /**
     * @param $id
     * @return string
     */
    public static function getUrlById($id)
    {
        return Url::to(['/voip/number/edit', 'id' => $id]);
    }

    /**
     * Вернуть кол-во звонков за месяц
     * @param string $month %02d
     * @return int
     */
    public function getCallsWithoutUsagesByMonth($month)
    {
        if (is_null($this->callsCount)) {
            $this->callsCount = \app\models\Number::dao()->getCallsWithoutUsages($this->city->connection_point_id, $this->number);
        }
        foreach ($this->callsCount as $calls) {
            if ($calls['m'] === $month) {
                return $calls['c'];
            }
        }
        return '';
    }
}
