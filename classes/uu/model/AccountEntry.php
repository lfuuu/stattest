<?php

namespace app\classes\uu\model;

use app\models\Language;
use app\models\usages\UsageInterface;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * Бухгалтерская проводка
 * Объединяет предварительное списание (транзакции) для одной услуги и типу (подключение, абонентка, каждый ресурс) по календарным месяцам
 *
 * @link http://bugtracker.welltime.ru/jira/browse/BIL-1909
 * Счет на postpaid никогда не создается
 * При подключении новой услуги prepaid сразу же создается счет на эту услугу. Если в течение календарных суток подключается вторая услуга, то она добавляется в первый счет.
 *      Если в новые календарные сутки - создается новый счет. В этот счет идет подключение подключение и абонентка. Ресурсы и минималка никогда сюда не попадают.
 * 1го числа каждого месяца создается новый счет за все prepaid абонентки, не вошедшие в отдельные счета (то есть абонентки автопродлеваемых услуг), все ресурсы и минималки.
 *      Подключение в этот счет не должно попасть.
 * Из любого счета всегда исключаются строки с нулевой стоимостью. Если в счете нет ни одной строки - он автоматически удаляется.
 *
 * Иными словами можно сказать:
 * проводки за подключение группируются посуточно и на их основе создаются счета. В эти же счета добавляются проводки за абонентку от этих же услуг за эту же дату
 * все остальные проводки (is_default) группируются помесячно и на их основе создаются счета.
 *
 * @property int $id
 * @property string $date У обычной проводки (is_default) важен только месяц, день всегда 1. У проводки на доплату (когда создается новая услуга) - день фактический.
 * @property int $account_tariff_id
 * @property int $type_id Если положительное, то TariffResource, иначе подключение или абонентка. Поэтому нет FK
 * @property float $price
 * @property float $price_without_vat
 * @property int $vat_rate
 * @property float $vat
 * @property float $price_with_vat
 * @property string $update_time
 * @property int $is_default
 * @property int $bill_id
 *
 * @property string $date_from Минимальная дата транзакций
 * @property string $date_to Максимальная дата транзакций
 * @property Bill $bill
 * @property AccountTariff $accountTariff
 * @property TariffResource $tariffResource
 * @property AccountLogSetup[] $accountLogSetups
 * @property AccountLogPeriod[] $accountLogPeriods
 * @property AccountLogResource[] $accountLogResources
 * @property AccountLogMin[] $accountLogMins
 * @property string name
 * @property string fullName
 */
class AccountEntry extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    const TYPE_ID_SETUP = -1;
    const TYPE_ID_PERIOD = -2;
    const TYPE_ID_MIN = -3;

    const NAME_RESOURCES = 'Resource';

    public static $names = [
        self::TYPE_ID_SETUP => 'Connection',
        self::TYPE_ID_PERIOD => 'Subscription fee',
        self::TYPE_ID_MIN => 'Minimal fee',
    ];

    protected $dateFrom = null;
    protected $dateTo = null;

    public static function tableName()
    {
        return 'uu_account_entry';
    }

    public function rules()
    {
        return [
            [['account_tariff_id', 'type_id', 'is_default'], 'integer'],
            [['price'], 'double'],
            [['date'], 'string', 'max' => 255],
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountTariff()
    {
        return $this->hasOne(AccountTariff::className(), ['id' => 'account_tariff_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getBill()
    {
        return $this->hasOne(Bill::className(), ['id' => 'bill_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTariffResource()
    {
        return $this->hasOne(TariffResource::className(), ['id' => 'type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccountLogSetups()
    {
        return $this->hasMany(AccountLogSetup::className(), ['account_entry_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccountLogPeriods()
    {
        return $this->hasMany(AccountLogPeriod::className(), ['account_entry_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccountLogResources()
    {
        return $this->hasMany(AccountLogResource::className(), ['account_entry_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccountLogMins()
    {
        return $this->hasMany(AccountLogMin::className(), ['account_entry_id' => 'id']);
    }

    /**
     * Вернуть название на нужном языке
     * Например, "Ресурс", "Подключение", "Подключение номера ..."
     * @return string
     */
    public function getName($langCode = Language::LANGUAGE_DEFAULT)
    {
        $names = [];

        // Например, "Номер ..."
        $accountTariff = $this->accountTariff;
        if ($accountTariff->service_type_id == ServiceType::ID_VOIP) {
            // телефония
            $names[] = Yii::t('uu', 'Number {number}', ['number' => $accountTariff->voip_number], $langCode);
        } elseif ($accountTariff->service_type_id == ServiceType::ID_VOIP_PACKAGE) {
            // пакет телефонии. Номер взять от телефонии
            $names[] = Yii::t('uu', 'Number {number}', ['number' => $accountTariff->prevAccountTariff->voip_number], $langCode);
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
     * @param string $langCode
     * @return string
     */
    public function getFullName($langCode = Language::LANGUAGE_DEFAULT)
    {
        $accountTariff = $this->accountTariff;

        $names = [];

        // Например, "ВАТС" или "SMS"
        // Кроме "Телефония" и "Пакет телефонии". Чтобы было короче. А для них и так помятно, ибо указан номер
        if (!in_array($accountTariff->service_type_id, [ServiceType::ID_VOIP, ServiceType::ID_VOIP_PACKAGE])) {
            $serviceType = $accountTariff->serviceType;
            $names[] = Yii::t('models/' . ServiceType::tableName(), 'Type #' . $serviceType->id, [], $langCode);
        }

        // Например, "Абонентская плата" или "Подключение" или "Номер 1234567890. Звонки"
        $names[] = $this->name;

        // Например, "Тариф «Максимальный»"
        // Кроме звонков. Чтобы было короче. У них тарификация зависит от пакета, а не тарифа
        if ($this->type_id < 0 || !in_array($this->tariffResource->resource_id, [Resource::ID_VOIP_CALLS, Resource::ID_TRUNK_CALLS])) {

            // в данный момент у услуги может не быть тарифа (она закрыта). Поэтому тариф надо брать не от услуги, а от транзакции
            $tariffPeriod = $this->getTariffPeriod();
            $name = Yii::t('uu', 'Tariff «{tariff}»', ['tariff' => $tariffPeriod->tariff->name], $langCode);

            // Например, "100". @todo "руб/мес" или "форинтов/год"
            // Только для абонентки за неполный месяц
//            if ($this->type_id == self::TYPE_ID_PERIOD && (new \DateTimeImmutable($this->date_from))->format('j') !== '1') {
//                $name .= ' (' . $tariffPeriod->price_per_period . ')';
//            }

            $names[] = $name;

        }

        return implode('. ', $names);
    }

    public function getTariffPeriod()
    {
        switch ($this->type_id) {
            case AccountEntry::TYPE_ID_SETUP:
                $accountLogSetups = $this->accountLogSetups;
                $accountLogSetup = reset($accountLogSetups);
                return $accountLogSetup->tariffPeriod;
                break;

            case AccountEntry::TYPE_ID_PERIOD:
                $accountLogPeriods = $this->accountLogPeriods;
                $accountLogPeriod = reset($accountLogPeriods);
                return $accountLogPeriod->tariffPeriod;
                break;

            case AccountEntry::TYPE_ID_MIN:
                $accountLogMins = $this->accountLogMins;
                $accountLogMin = reset($accountLogMins);
                return $accountLogMin->tariffPeriod;
                break;

            default:
                $accountLogResources = $this->accountLogResources;
                $accountLogResource = reset($accountLogResources);
                return $accountLogResource->tariffPeriod;
                break;
        }
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
                $accountLogPeriods = $this->accountLogPeriods;
                return reset($accountLogPeriods)->coefficient;

            case self::TYPE_ID_MIN:
                $accountLogMins = $this->accountLogMins;
                return reset($accountLogMins)->coefficient;

            default:
                // ресурсы
                if (
                    ($tariffResource = $this->tariffResource) &&
                    ($resource = $tariffResource->resource)
                ) {
                    if (in_array($resource->id, [Resource::ID_VOIP_CALLS, Resource::ID_TRUNK_CALLS])) {
                        // В звонках указана стоимость, но не минуты
                        return 1;
                    }

                    $accountLogResources = array_filter($this->accountLogResources, function (AccountLogResource $accountLogResource) {
                        return $accountLogResource->amount_overhead;
                    });

                    if (!count($accountLogResources)) {
                        return 0;
                    }

                    return array_reduce($accountLogResources, function ($summary, AccountLogResource $accountLogResource) {
                        $summary = (float)$summary;
                        return $summary + $accountLogResource->amount_overhead;
                    }) / count($accountLogResources);
                } else {
                    Yii::error('Wrong AccountEntry.Type ' . $this->type_id . ' for ID ' . $this->id);
                    return 0;
                }
        }
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

                return Yii::t('models/' . Resource::tableName(), Resource::DEFAULT_UNIT, [], $langCode);

            default:
                if (
                    ($tariffResource = $this->tariffResource) &&
                    ($resource = $tariffResource->resource)
                ) {
                    return Yii::t('models/' . Resource::tableName(), $resource->unit, [], $langCode);
                } else {
                    Yii::error('Wrong AccountEntry.Type ' . $this->type_id . ' for ID ' . $this->id);
                    return '';
                }
                break;
        }
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return Url::to(['uu/account-entry', 'AccountEntryFilter[id]' => $this->id]);
    }

    /**
     * Вернуть минимальную дату транзакции
     * метод для "date_from"
     */
    public function getDate_from()
    {
        if (!$this->dateFrom) {
            $this->setDateFromTo();
        }
        return $this->dateFrom;

    }

    /**
     * Вернуть максимальную дату транзакции
     * метод для "date_to"
     */
    public function getDate_to()
    {
        if (!$this->dateTo) {
            $this->setDateFromTo();
        }
        return $this->dateTo;

    }

    /**
     * Найти и установить минимальную и максимальную дату транзакции
     */
    protected function setDateFromTo()
    {
        $this->dateFrom = UsageInterface::MAX_POSSIBLE_DATE;
        $this->dateTo = UsageInterface::MIN_DATE;

        switch ($this->type_id) {
            case AccountEntry::TYPE_ID_SETUP:
                foreach ($this->accountLogSetups as $accountLogSetup) {
                    $this->dateFrom = min($this->dateFrom, $accountLogSetup->date);
                    $this->dateTo = max($this->dateTo, $accountLogSetup->date);
                }
                break;

            case AccountEntry::TYPE_ID_PERIOD:
                foreach ($this->accountLogPeriods as $accountLogPeriod) {
                    $this->dateFrom = min($this->dateFrom, $accountLogPeriod->date_from);
                    $this->dateTo = max($this->dateTo, $accountLogPeriod->date_to);
                }
                break;

            case AccountEntry::TYPE_ID_MIN:
                foreach ($this->accountLogMins as $accountLogMin) {
                    $this->dateFrom = min($this->dateFrom, $accountLogMin->date_from);
                    $this->dateTo = max($this->dateTo, $accountLogMin->date_to);
                }
                break;

            default:
                foreach ($this->accountLogResources as $accountLogResource) {
                    $this->dateFrom = min($this->dateFrom, $accountLogResource->date);
                    $this->dateTo = max($this->dateTo, $accountLogResource->date);
                }
                break;
        }
    }
}
