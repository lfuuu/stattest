<?php

namespace app\models;

use app\dao\NumberDao;
use app\models\light_models\NumberPriceLight;
use app\modules\nnp\models\NdcType;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * Class Number
 *
 * @property string $number
 * @property string $status
 * @property string $reserve_from
 * @property string $reserve_till
 * @property string $hold_from
 * @property string $hold_to
 * @property int $beauty_level
 * @property int $region
 * @property int $client_id
 * @property int $usage_id
 * @property int $uu_account_tariff_id
 * @property string $reserved_free_date
 * @property string $used_until_date
 * @property int $edit_user_id
 * @property int $city_id
 * @property int $did_group_id
 * @property string $number_tech
 * @property int $ndc
 * @property string $number_subscriber
 * @property int $ndc_type_id
 * @property string $date_start
 * @property string $date_end
 * @property int $operator_account_id
 * @property int $country_code
 * @property int $calls_per_month_0 Кол-во звонков за текущий месяц
 * @property int $calls_per_month_1 Кол-во звонков за -1 месяц
 * @property int $calls_per_month_2 Кол-во звонков за -2 месяц
 * @property int $is_ported
 * @property int $is_service
 * @property integer $trunk_id
 *
 * @property City $city
 * @property Country $country
 * @property DidGroup $didGroup
 * @property UsageVoip $usage
 * @property ClientAccount $clientAccount
 * @property NdcType $ndcType
 *
 * @property float $originPrice
 * @property float $price
 * @property array $priceWithCurrency
 */
class Number extends ActiveRecord
{
    const STATUS_NOTSALE = 'notsale';
    const STATUS_INSTOCK = 'instock';
    const STATUS_ACTIVE_CONNECTED = 'active_connected';
    const STATUS_ACTIVE_TESTED = 'active_tested';
    const STATUS_ACTIVE_COMMERCIAL = 'active_commercial';
    const STATUS_NOTACTIVE_RESERVED = 'notactive_reserved';
    const STATUS_NOTACTIVE_HOLD = 'notactive_hold';
    const STATUS_RELEASED = 'released';

    const STATUS_GROUP_ACTIVE = 'active';
    const STATUS_GROUP_NOTACTIVE = 'notactive';

    const NUMBER_MAX_LINE = 10000; // если Number до этого числа - это линия, если больше - номер

    public static $statusList = [
        self::STATUS_NOTSALE => 'Не продается',
        self::STATUS_INSTOCK => 'Свободен',
        self::STATUS_ACTIVE_TESTED => 'Используется. Тестируется.',
        self::STATUS_ACTIVE_COMMERCIAL => 'Используется. В коммерции.',
        self::STATUS_ACTIVE_CONNECTED => 'Подключение запланировано',
        self::STATUS_NOTACTIVE_RESERVED => 'В резерве',
        self::STATUS_NOTACTIVE_HOLD => 'В отстойнике',
        self::STATUS_RELEASED => 'Откреплен',
    ];

    public static $statusGroup = [
        self::STATUS_GROUP_ACTIVE => [self::STATUS_ACTIVE_CONNECTED, self::STATUS_ACTIVE_TESTED, self::STATUS_ACTIVE_COMMERCIAL],
        self::STATUS_GROUP_NOTACTIVE => [self::STATUS_NOTACTIVE_RESERVED, self::STATUS_NOTACTIVE_HOLD],
    ];

    protected $callsCount = null;

    public $levenshtein = -1;

    const TYPE_NUMBER = 'number';
    const TYPE_7800 = '7800';
    const TYPE_LINE = 'line';

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'voip_numbers';
    }

    /**
     * Вернуть имена полей
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'number' => 'Номер',
            'client_id' => 'Клиент',
            'usage_id' => 'Услуга',
            'city_id' => 'Город',
            'did_group_id' => 'DID-группа',
            'beauty_level' => 'Степень красивости',
            'status' => 'Статус',
            'ndc_type_id' => 'Тип номера',
            'number_tech' => 'Технический номер',
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['status', 'number_tech'], 'string'],
            [['beauty_level'], 'integer'],
            [['status', 'beauty_level'], 'required', 'on' => 'save'],
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
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => 'country_code']);
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
    public function getNdcType()
    {
        return $this->hasOne(NdcType::className(), ['id' => 'ndc_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['id' => 'client_id']);
    }

    /**
     * @param string|null $currency
     * @param ClientAccount $clientAccount
     * @return float|null
     */
    public function getPrice($currency = null, ClientAccount $clientAccount = null)
    {
        try {
            $price = $this->getOriginPrice($clientAccount);
        } catch (\Exception $e) {
            return null;
        }

        if (!is_null($currency) && $this->didGroup->country->currency_id != $currency) {
            if (($tariffCurrencyRate = CurrencyRate::dao()->getRate($this->didGroup->country->currency_id))) {
                $price *= $tariffCurrencyRate;
            }

            if (($currencyRate = CurrencyRate::dao()->getRate($currency))) {
                $price /= $currencyRate;
            }
        }

        return $price;
    }

    /**
     * @param string $currency
     * @param ClientAccount $clientAccount
     * @return NumberPriceLight
     */
    public function getPriceWithCurrency($currency = Currency::RUB, $clientAccount = null)
    {
        $formattedResult = new NumberPriceLight;
        $formattedResult->setAttributes([
            'currency' => $currency,
            'price' => $this->getPrice($currency, $clientAccount),
        ]);
        return $formattedResult;
    }

    /**
     * @param ClientAccount $clientAccount
     * @return float
     */
    public function getOriginPrice($clientAccount = null)
    {
        $priceField = 'price' . max(ClientAccount::DEFAULT_PRICE_LEVEL, $clientAccount ? $clientAccount->price_level : ClientAccount::DEFAULT_PRICE_LEVEL);
        return (float)$this->didGroup->{$priceField};
    }

    /**
     * @param ClientAccount $clientAccount
     * @return NumberPriceLight
     */
    public function getOriginPriceWithCurrency($clientAccount = null)
    {
        $formattedResult = new NumberPriceLight;
        try {

            $formattedResult->setAttributes([
                'currency' => $this->didGroup->country->currency_id,
                'price' => $this->getOriginPrice($clientAccount),
            ]);

        } catch (\Exception $e) {
            $formattedResult->setAttributes([
                'currency' => null,
                'price' => null,
            ]);
        }

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
     * Ссылка на страницу номера
     *
     * @param int $id
     * @return string
     */
    public static function getUrlById($id)
    {
        return Url::to(['/voip/number/view', 'did' => $id]);
    }

    /**
     * Вернуть кол-во звонков за месяц
     *
     * @param string $month %02d
     * @return int
     */
    public function getCallsWithoutUsagesByMonth($month)
    {
        if (is_null($this->callsCount)) {
            $this->callsCount = self::dao()->getCallsWithoutUsages($this->city->connection_point_id, $this->number);
        }

        foreach ($this->callsCount as $calls) {
            if ($calls['m'] === $month) {
                return $calls['c'];
            }
        }

        return '';
    }

    /**
     * Получаем лог изменений состояния номера
     *
     * @return array
     * @throws \yii\db\Exception
     */
    public function getChangeStatusLog()
    {
        return self::dao()->getChangeStateLog($this);
    }

    /**
     * Мобильный и непортированный - значит, наш. Тогда FMC можно включить/выключить по желанию юзера
     *
     * @return bool
     */
    public function isFmcEditable()
    {
        return $this->ndc_type_id == NdcType::ID_MOBILE && !$this->is_ported;
    }

    /**
     * Мобильный и портированный - FMC всегда включен.
     *
     * @return bool
     */
    public function isFmcAlwaysActive()
    {
        return $this->ndc_type_id == NdcType::ID_MOBILE && $this->is_ported;
    }

    /**
     * Немобильный - FMC всегда выключен.
     *
     * @return bool
     */
    public function isFmcAlwaysInactive()
    {
        return $this->ndc_type_id != NdcType::ID_MOBILE;
    }
}
