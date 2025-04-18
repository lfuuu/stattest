<?php

namespace app\modules\uu\models;

use app\classes\model\ActiveRecord;
use app\models\BillLine;
use app\models\Language;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\Url;

/**
 * Универсальная проводка
 * Объединяет предварительное списание (транзакции) для одной услуги и типу (подключение, абонентка, каждый ресурс) по календарным месяцам
 *
 * @property int $id
 * @property int $operation_type_id
 * @property string $date Важен только месяц, день всегда 1
 * @property int $account_tariff_id
 * @property int $tariff_period_id Кэш accountTariff.tariff_period_id на эту дату
 * @property int $type_id Если положительное, то TariffResource, иначе подключение или абонентка. Поэтому нет FK
 * @property float $price Цена по тарифу
 * @property float $cost_price
 * @property float $price_without_vat Цена без НДС
 * @property int $vat_rate
 * @property float $vat НДС
 * @property float $price_with_vat Цена с НДС
 * @property string $update_time
 * @property int $is_next_month
 * @property int $bill_id
 * @property string $date_from Минимальная дата транзакций
 * @property string $date_to Максимальная дата транзакций
 *
 * @property-read Bill $bill
 * @property-read BillLine $billLine
 * @property-read AccountTariff $accountTariff
 * @property-read TariffPeriod $tariffPeriod
 * @property-read TariffResource $tariffResource
 * @property-read AccountLogSetup[] $accountLogSetups
 * @property-read AccountLogPeriod[] $accountLogPeriods
 * @property-read AccountLogResource[] $accountLogResources
 * @property-read AccountLogMin[] $accountLogMins
 * @property-read string $name
 * @property-read string $fullName
 *
 * @method static AccountEntry findOne($condition)
 * @method static AccountEntry[] findAll($condition)
 */
class AccountEntry extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    const TYPE_ID_SETUP = -1; // Подключение
    const TYPE_ID_PERIOD = -2; // Абонентская плата
    const TYPE_ID_MIN = -3; // Минимальная плата

    const AMOUNT_PRECISION = 8;

    const NAME_RESOURCES = 'Resource';

    public static $names = [
        self::TYPE_ID_SETUP => 'Setup',
        self::TYPE_ID_PERIOD => 'Subscription fee',
        self::TYPE_ID_MIN => 'Minimal fee',
    ];

    protected $dateFrom = null;
    protected $dateTo = null;

    protected $isAttributeTypecastBehavior = true;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_account_entry';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['operation_type_id', 'account_tariff_id', 'type_id', 'is_next_month'], 'integer'],
            [['price', 'cost_price'], 'double'],
            [['date'], 'string', 'max' => 255],
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountTariff()
    {
        return $this->hasOne(AccountTariff::class, ['id' => 'account_tariff_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getTariffPeriod()
    {
        return $this->hasOne(TariffPeriod::class, ['id' => 'tariff_period_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getBill()
    {
        return $this->hasOne(Bill::class, ['id' => 'bill_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getBillLine()
    {
        return $this->hasOne(BillLine::class, ['uu_account_entry_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTariffResource()
    {
        return $this->hasOne(TariffResource::class, ['id' => 'type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccountLogSetups()
    {
        return $this->hasMany(AccountLogSetup::class, ['account_entry_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccountLogPeriods()
    {
        return $this->hasMany(AccountLogPeriod::class, ['account_entry_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccountLogResources()
    {
        return $this->hasMany(AccountLogResource::class, ['account_entry_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccountLogMins()
    {
        return $this->hasMany(AccountLogMin::class, ['account_entry_id' => 'id']);
    }

    /**
     * Вернуть название на нужном языке
     * Например, "Ресурс", "Подключение", "Подключение номера ..."
     *
     * @param string $langCode
     * @param bool $isFullDocument для полного документа детализация включает номер.
     * @return string
     */
    public function getName($langCode = Language::LANGUAGE_DEFAULT, $isFullDocument = true)
    {
        $names = [];

        if ($isFullDocument) {
            // Например, "Номер ..."
            $accountTariff = $this->accountTariff;
            if ($accountTariff->service_type_id == ServiceType::ID_VOIP) {
                // телефония
                $names[] = Yii::t('uu', 'Number {number}', ['number' => $accountTariff->voip_number], $langCode);
            } elseif (in_array($accountTariff->service_type_id, [ServiceType::ID_VOIP_PACKAGE_CALLS, ServiceType::ID_VOIP_PACKAGE_INTERNET_ROAMABILITY, ServiceType::ID_VOIP_PACKAGE_INTERNET, ServiceType::ID_VOIP_PACKAGE_SMS])) {
                // пакет телефонии. Номер взять от телефонии
                $names[] = Yii::t('uu', 'Number {number}', ['number' => $accountTariff->prevAccountTariff->voip_number], $langCode);
            }
        }

        if ($this->type_id > 0) {
            // ресурс
            // Например, "Звонки"
            $tariffResource = $this->tariffResource;
            $resource = $tariffResource->resource;
            $names[] = $resource->getFullName($langCode, (bool)$tariffResource->amount);

        } else {
            // подключение/абонентка/минималка
            // Например, "Подключение"
            $name = self::$names[$this->type_id];
            $names[] = Yii::t('uu', $name, [], $langCode);
        }

        return implode('. ', $names);

    }

    /**
     * Вернуть тип текстом
     *
     * @param string $langCode
     * @param bool $isFullDocument
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getFullName($langCode = null, $isFullDocument = true)
    {
        if (is_null($langCode)) {
            $langCode = $this->accountTariff->clientAccount->contragent->lang_code;
        }

        $accountTariff = $this->accountTariff;

        $names = [];

        // Например, "ВАТС" или "SMS"
        // Кроме "Телефония" и "Пакет телефонии". Чтобы было короче. А для них и так понятно, ибо указан номер
        // Кроме "Разовая услуга" - там нужен только комментарий менеджера
        if (!in_array($accountTariff->service_type_id, [ServiceType::ID_VOIP_PACKAGE_CALLS, ServiceType::ID_ONE_TIME])) {
            $serviceType = $accountTariff->serviceType;

            $tmp1StartDate = new \DateTimeImmutable('2025-01-01 00:00:00');
            $entryDate = new \DateTimeImmutable($this->date);

            if ($langCode == Language::LANGUAGE_RUSSIAN && $tmp1StartDate <= $entryDate) {
                $serviceTypeName = trim($serviceType->name);
            } else {
                $serviceTypeName = trim(Yii::t('models/' . ServiceType::tableName(), 'Type #' . $serviceType->id, [], $langCode));
            }
        } else {
            $serviceTypeName = '';
        }

        // в данный момент у услуги может не быть тарифа (она закрыта). Поэтому тариф надо брать не от услуги, а от транзакции
        $tariffPeriod = $this->tariffPeriod;
        /** @var Tariff $tariff */
        $tariff = $tariffPeriod->tariff;

        if ($tariff->service_type_id == ServiceType::ID_ONE_TIME) {
            // "Разовая услуга" - там нужен только комментарий менеджера
            $names[] = $accountTariff->comment;
        } elseif (array_key_exists($tariff->service_type_id, ServiceType::$packages)) {
            // пакет
            $names[] = Yii::t('uu', 'Package «{tariff}»', ['tariff' => $tariff->name], $langCode);
        } else {
            // тариф
            $names[] = Yii::t('uu', 'Tariff «{tariff}»', ['tariff' => $tariff->name], $langCode);
        }

        // Например, "Абонентская плата" или "Подключение" или "Номер 1234567890. Звонки"
        // Кроме "Разовая услуга" - там нужен только комментарий менеджера
        if ($accountTariff->service_type_id != ServiceType::ID_ONE_TIME) {
            $names[] = $this->getName($langCode, $isFullDocument);
        }

        // Сохранить \yii\i18n\Formatter locale
        $locale = Yii::$app->formatter->locale;
        // Установить \yii\i18n\Formatter locale = $langCode
        Yii::$app->formatter->locale = $langCode;

        // Например, "25 марта" или "1-31 окт."
        // Кроме "Разовая услуга" - там нужен только комментарий менеджера
        if ($accountTariff->service_type_id != ServiceType::ID_ONE_TIME) {
            $names[] = (($this->date_from != $this->date_to) ? Yii::$app->formatter->asDate($this->date_from, 'php:j') . '-' : '') .
                Yii::$app->formatter->asDate($this->date_to, 'php:j M Y');
        }

        // Восстановить \yii\i18n\Formatter locale
        Yii::$app->formatter->locale = $locale;

        return ($serviceTypeName ? $serviceTypeName . ': ' : '') .
            implode('. ', $names);
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        switch ($this->type_id) {
            case self::TYPE_ID_SETUP:
                return 1;

            case self::TYPE_ID_PERIOD:
                /** @var AccountLogPeriod[] $accountLogPeriods */
                $accountLogPeriods = $this->getAccountLogPeriods()->andWhere(['>', 'price', 0])->all();
                $cnt = count($accountLogPeriods);
                if (!$cnt) {
                    // слишком старое. Транзакции уже почистили
                    return 1;
                }

                $amount = $cnt * reset($accountLogPeriods)->coefficient;
                return round($amount, self::AMOUNT_PRECISION);

            case self::TYPE_ID_MIN:
                /** @var AccountLogMin[] $accountLogMins */
                $accountLogMins = $this->getAccountLogMins()->andWhere(['>', 'price', 0])->all();
                $cnt = count($accountLogMins);
                if (!$accountLogMins) {
                    // слишком старое. Транзакции уже почистили
                    return 1;
                }

                $amount = $cnt * reset($accountLogMins)->coefficient;
                return round($amount, self::AMOUNT_PRECISION);

            default:
                // ресурсы
                if (
                    ($tariffResource = $this->tariffResource)
                    && ($resource = $tariffResource->resource)
                ) {
                    if (array_key_exists($resource->id, ResourceModel::$calls)) {
                        // В звонках указана стоимость, но не минуты
                        return 1;
                    }

                    /** @var AccountLogResource[] $accountLogResources */
                    $accountLogResources = $this->getAccountLogResources()->andWhere(['>', 'amount_overhead', 0])->all();
                    if (!count($accountLogResources)) {
                        return $this->_getAmountFromBillLines($this->id);
                    }

                    $amount = array_reduce(
                            $accountLogResources,
                            function ($summary, AccountLogResource $accountLogResource) {
                                $summary = (float)$summary;
                                return ($summary + $accountLogResource->amount_overhead);
                            }
                        ) / count($accountLogResources);
                    return round($amount, self::AMOUNT_PRECISION);
                }

                Yii::error('Wrong AccountEntry.Type ' . $this->type_id . ' for ID ' . $this->id);
                return 0;
        }
    }

    /**
     * @param integer $accountEntryId
     * @return float
     */
    private function _getAmountFromBillLines($accountEntryId)
    {
        // Т.к. логи трутся за предыдущий месяц на 3е число нового месяца, данные о количестве недоступно.
        // Забераем из строк счета.
        // @TODO
        return $this->billLine ? round($this->billLine->amount, 2) : 0;
    }

    /**
     * @param string $langCode
     * @return string
     */
    public function getTypeUnitName($langCode = Language::LANGUAGE_DEFAULT)
    {
        switch ($this->type_id) {
            case self::TYPE_ID_SETUP:
            case self::TYPE_ID_PERIOD:
            case self::TYPE_ID_MIN:

                return Yii::t('models/' . ResourceModel::tableName(), ResourceModel::DEFAULT_UNIT, [], $langCode);

            default:
                if (
                    ($tariffResource = $this->tariffResource) &&
                    ($resource = $tariffResource->resource)
                ) {
                    return Yii::t('models/' . ResourceModel::tableName(), $resource->unit, [], $langCode);
                }

                Yii::error('Wrong AccountEntry.Type ' . $this->type_id . ' for ID ' . $this->id);
                return '';
        }
    }

    /**
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function getUrl()
    {
        return Url::to(['/uu/account-entry', 'AccountEntryFilter[id]' => $this->id]);
    }

    /**
     * @return array
     */
    public static function getHelpConfluence()
    {
        return ['confluenceId' => 6127902, 'message' => 'Проводки'];
    }
}
