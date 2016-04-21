<?php

namespace app\classes\uu\model;

use app\classes\Html;
use app\models\Country;
use app\models\Currency;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\Url;

/**
 * Тариф (ВАТС лайт, ВАТС про и пр.)
 *
 * @property integer $id
 * @property string $name
 * @property integer $service_type_id
 * @property integer $tariff_status_id
 * @property integer $n_prolongation_periods
 * @property string $currency_id
 * @property integer $count_of_validity_period
 * @property integer $country_id
 * @property integer $tariff_person_id
 *
 * @property integer $is_autoprolongation
 * @property integer $is_charge_after_blocking
 * @property integer $is_charge_after_period
 * @property integer $is_include_vat
 * @property integer $is_default
 *
 * @property Currency $currency
 * @property TariffResource[] $tariffResources
 * @property ServiceType $serviceType
 * @property Country $country
 * @property TariffStatus $status
 * @property TariffPerson $group
 * @property TariffPeriod[] $tariffPeriods
 *
 * VOIP only!
 * @property integer $voip_tarificate_id
 * @property integer $voip_group_id
 *
 * @property TariffVoipTarificate $voipTarificate
 * @property TariffVoipGroup $voipGroup
 * @property TariffVoipCity[] $voipCities
 */
class Tariff extends \yii\db\ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    // Методы для полей insert_time, insert_user_id, update_time, update_user_id
    use \app\classes\traits\InsertUpdateUserTrait;

    // на сколько сдвинуть id при конвертации
    const DELTA_VPBX = 0;
    const DELTA_VOIP = 1000;
    const DELTA_VOIP_PACKAGE = 2000;

    const DELTA_INTERNET = 3000;
    const DELTA_COLLOCATION = 3000;
    const DELTA_VPN = 3000;

    const DELTA_IT_PARK = 4000;
    const DELTA_DOMAIN = 4000;
    const DELTA_MAILSERVER = 4000;
    const DELTA_ATS = 4000;
    const DELTA_SITE = 4000;
    const DELTA_SMS_GATE = 4000;
    const DELTA_USPD = 4000;
    const DELTA_WELLSYSTEM = 4000;
    const DELTA_WELLTIME = 4000;
    const DELTA_EXTRA = 4000;

    const DELTA_SMS = 5000;

    const NUMBER_TYPE_NUMBER = 'number';
    const NUMBER_TYPE_7800 = '7800';
    const NUMBER_TYPE_LINE = 'line';


    public $serviceIdToDelta = [
        ServiceType::ID_VPBX => self::DELTA_VPBX,
        ServiceType::ID_VOIP => self::DELTA_VOIP,
        ServiceType::ID_VOIP_PACKAGE => self::DELTA_VOIP_PACKAGE,

        ServiceType::ID_INTERNET => self::DELTA_INTERNET,
        ServiceType::ID_COLLOCATION => self::DELTA_COLLOCATION,
        ServiceType::ID_VPN => self::DELTA_VPN,

        ServiceType::ID_IT_PARK => self::DELTA_IT_PARK,
        ServiceType::ID_DOMAIN => self::DELTA_DOMAIN,
        ServiceType::ID_MAILSERVER => self::DELTA_MAILSERVER,
        ServiceType::ID_ATS => self::DELTA_ATS,
        ServiceType::ID_SITE => self::DELTA_SITE,
        ServiceType::ID_SMS_GATE => self::DELTA_SMS_GATE,
        ServiceType::ID_USPD => self::DELTA_USPD,
        ServiceType::ID_WELLSYSTEM => self::DELTA_WELLSYSTEM,
        ServiceType::ID_WELLTIME => self::DELTA_WELLTIME,
        ServiceType::ID_EXTRA => self::DELTA_EXTRA,

        ServiceType::ID_SMS => self::DELTA_SMS,
    ];

    public $serviceIdToUrl = [
        ServiceType::ID_VPBX => '/?module=tarifs&action=edit&m=virtpbx&id=%d',
        ServiceType::ID_VOIP => '/tariff/voip/edit?id=%d',
        ServiceType::ID_VOIP_PACKAGE => '/tariff/voip-package/edit?id=%d',

        ServiceType::ID_INTERNET => '/?module=tarifs&action=edit&m=internet&id=%d',
        ServiceType::ID_COLLOCATION => '/?module=tarifs&action=edit&m=internet&id=%d',
        ServiceType::ID_VPN => '/?module=tarifs&action=edit&m=internet&id=%d',

        ServiceType::ID_IT_PARK => '/?module=tarifs&action=edit&m=itpark&id=%d',
        ServiceType::ID_DOMAIN => '/?module=tarifs&action=edit&m=extra&id=%d',
        ServiceType::ID_MAILSERVER => '/?module=tarifs&action=edit&m=extra&id=%d',
        ServiceType::ID_ATS => '/?module=tarifs&action=edit&m=extra&id=%d',
        ServiceType::ID_SITE => '/?module=tarifs&action=edit&m=extra&id=%d',
        ServiceType::ID_SMS_GATE => '/?module=tarifs&action=edit&m=extra&id=%d',
        ServiceType::ID_USPD => '/?module=tarifs&action=edit&m=extra&id=%d',
        ServiceType::ID_WELLSYSTEM => '/?module=tarifs&action=edit&m=wellsystem&id=%d',
        ServiceType::ID_WELLTIME => '/?module=tarifs&action=edit&m=welltime&id=%d',
        ServiceType::ID_EXTRA => '/?module=tarifs&action=edit&m=extra&id=%d',

        ServiceType::ID_SMS => '/?module=tarifs&action=edit&m=sms&id=%d',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'uu_tariff';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'service_type_id',
                    'tariff_status_id',
                    'n_prolongation_periods',
                    'count_of_validity_period',
                    'tariff_person_id',
                    'is_autoprolongation',
                    'is_include_vat',
                    'is_charge_after_blocking',
                    'is_charge_after_period',
                    'is_default',
                    'country_id'
                ],
                'integer'
            ],
            [['voip_tarificate_id', 'voip_group_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['currency_id'], 'string', 'max' => 3],
            [['name'], 'required'],
        ];
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return Url::to(['uu/tariff/edit', 'id' => $this->id]);
    }

    /**
     * @return string
     */
    public static function getUrlNew($serviceTypeId)
    {
        return Url::to(['uu/tariff/new', 'serviceTypeId' => $serviceTypeId]);
    }

    /**
     * @return ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['id' => 'currency_id']);
    }

    /**
     * @param $resourceId
     * @return ActiveQuery
     */
    public function getTariffResource($resourceId)
    {
        return $this->getTariffResources()
            ->where('resource_id = :id', [':id' => $resourceId]);
    }

    /**
     * @return ActiveQuery
     */
    public function getTariffResources()
    {
        return $this->hasMany(TariffResource::className(), ['tariff_id' => 'id'])
            ->indexBy('id');
    }

    /**
     * @return ActiveQuery
     */
    public function getTariffPeriods()
    {
        return $this->hasMany(TariffPeriod::className(), ['tariff_id' => 'id'])
            ->indexBy('id');
    }

    /**
     * @return ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => 'country_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(TariffStatus::className(), ['id' => 'tariff_status_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(TariffPerson::className(), ['id' => 'tariff_person_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getServiceType()
    {
        return $this->hasOne(ServiceType::className(), ['id' => 'service_type_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getVoipTarificate()
    {
        return $this->hasOne(TariffVoipTarificate::className(), ['id' => 'voip_tarificate_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getVoipGroup()
    {
        return $this->hasOne(TariffVoipGroup::className(), ['id' => 'voip_group_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getVoipCities()
    {
        return $this->hasMany(TariffVoipCity::className(), ['tariff_id' => 'id'])
            ->indexBy('city_id');
    }

    /**
     * Вернуть ID неуниверсальной услуги
     * @return int
     */
    public function getNonUniversalId()
    {
        return $this->id - $this->serviceIdToDelta[$this->service_type_id];
    }

    /**
     * Вернуть html-ссылку на неуниверсальную услугу
     * @return string
     */
    public function getNonUniversalUrl()
    {
        $url = $this->serviceIdToUrl[$this->service_type_id];
        if (!$url) {
            return '';
        }

        $id = $this->getNonUniversalId();
        return Html::a($id, sprintf($url, $id));
    }

    /**
     * Вернуть список типов в зависимости от страны
     *
     * @param int $countryId
     * @param bool $isWithEmpty
     * @return array
     */
    public static function getVoipTypesByCountryId($countryId = null, $isWithEmpty = false)
    {
        $types = [
            self::NUMBER_TYPE_NUMBER => 'Номер',
            self::NUMBER_TYPE_7800 => '7800',
            self::NUMBER_TYPE_LINE => 'Линия без номера',
            //'operator' => 'Оператор',
        ];

        if ($isWithEmpty) {
            $types = ['' => ' ---- '] + $types;
        }

        if ($countryId && $countryId != Country::RUSSIA) {
            unset($types[self::NUMBER_TYPE_7800]); // 7800 только в России
        }

        if ($countryId && $countryId == Country::RUSSIA) {
            unset($types[self::NUMBER_TYPE_LINE]); // линия без номера только не в России
        }

        return $types;
    }
}