<?php

namespace app\classes\uu\model;

use app\models\Language;
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
 * @property Bill $bill
 * @property AccountTariff $accountTariff
 * @property TariffResource $tariffResource
 * @property AccountLogSetup[] $accountLogSetups
 * @property AccountLogPeriod[] $accountLogPeriods
 * @property AccountLogResource[] $accountLogResources
 * @property string typeName
 * @property string code
 */
class AccountEntry extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    const TYPE_ID_SETUP = -1;
    const TYPE_ID_PERIOD = -2;
    const TYPE_ID_MIN = -3;

    const CODE_TYPE_ID_SETUP = 'setup';
    const CODE_TYPE_ID_PERIOD = 'period';
    const CODE_TYPE_ID_MINIMUM = 'minimum';
    const CODE_TYPE_ID_RESOURCES = 'resource';

    private $codeNames = [
        self::TYPE_ID_SETUP => self::CODE_TYPE_ID_SETUP,
        self::TYPE_ID_PERIOD => self::CODE_TYPE_ID_PERIOD,
        self::TYPE_ID_MIN => self::CODE_TYPE_ID_MINIMUM,
    ];

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
     * @return string
     */
    public function getCode()
    {
        if (!isset($this->codeNames[$this->type_id])) {
            return self::CODE_TYPE_ID_RESOURCES;
        }
        return $this->codeNames[$this->type_id];
    }

    /**
     * Вернуть тип текстом
     * @param string $langCode
     * @return string
     */
    public function getTypeName($langCode = Language::LANGUAGE_DEFAULT)
    {
        $tableName = self::tableName();
        switch ($this->type_id) {
            case self::TYPE_ID_SETUP:
            case self::TYPE_ID_PERIOD:
            case self::TYPE_ID_MIN:

                $serviceTypeName = '';
                if (
                    ($accountTariff = $this->accountTariff)
                    && ($serviceType = $accountTariff->serviceType)
                ) {
                    $serviceTypeName = Yii::t('models/' . $serviceType::tableName(), 'Type #' . $serviceType->id, [], $langCode) . '. ';
                }

                $dictionary = 'models/' . $tableName;

                return Yii::t($dictionary, '{name} ({descr}). {serviceTypeName}', [
                    'name' => Yii::t($dictionary, $this->code, [], $langCode),
                    'serviceTypeName' => $serviceTypeName,
                    'descr' => ($this->accountTariff->service_type_id == ServiceType::ID_VOIP ? $this->accountTariff->voip_number : $this->account_tariff_id),
                ], $langCode);

            default: //resources
                if (
                    ($tariffResource = $this->tariffResource) &&
                    ($resource = $tariffResource->resource)
                ) {
                    return $resource->getFullName($langCode);
                } else {
                    Yii::error('Wrong AccountEntry.Type ' . $this->type_id . ' for ID ' . $this->id);
                    return '';
                }
        }
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        switch ($this->type_id) {
            case self::TYPE_ID_SETUP:
            case self::TYPE_ID_PERIOD:
            case self::TYPE_ID_MIN:

                return 1;

            //@todo Звонки обсчитываются некорректно. В транзакциях указана стоимость, но не минуты
            default:
                if (
                    ($tariffResource = $this->tariffResource) &&
                    ($resource = $tariffResource->resource)
                ) {
                    $accountLogResources = array_filter($this->accountLogResources, function(AccountLogResource $accountLogResource) {
                        return $accountLogResource->amount_overhead;
                    });

                    if (!count($accountLogResources)) {
                        return 0;
                    }

                    return array_reduce($accountLogResources, function($summary, AccountLogResource $accountLogResource) {
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
}
