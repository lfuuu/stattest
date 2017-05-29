<?php

namespace app\modules\uu\models;

use app\classes\model\HistoryActiveRecord;
use app\models\Country;
use app\models\Currency;
use app\modules\nnp\models\Package;
use app\modules\nnp\models\PackageMinute;
use app\modules\nnp\models\PackagePrice;
use app\modules\nnp\models\PackagePricelist;
use yii\db\ActiveQuery;
use yii\helpers\Url;

/**
 * Тариф (ВАТС лайт, ВАТС про и пр.)
 *
 * @property integer $id
 * @property string $name
 * @property integer $service_type_id
 * @property integer $tariff_status_id
 * @property string $currency_id
 * @property integer $count_of_validity_period
 * @property integer $country_id
 * @property integer $tariff_person_id
 *
 * @property integer $is_autoprolongation
 * @property integer $is_charge_after_blocking
 * @property integer $is_include_vat
 * @property integer $is_default
 * @property integer $is_postpaid
 *
 * @property Currency $currency
 * @property TariffResource[] $tariffResources
 * @property TariffResource[] $tariffResourcesIndexedByResourceId
 * @property ServiceType $serviceType
 * @property Country $country
 * @property TariffStatus $status
 * @property TariffPerson $person
 * @property TariffPeriod[] $tariffPeriods
 *
 * VOIP && VOIP package only!
 * @property integer $voip_group_id
 *
 * VOIP package only!
 * @property Package $package
 * @property PackageMinute[] $packageMinutes
 * @property PackagePrice[] $packagePrices
 * @property PackagePricelist[] $packagePricelists
 *
 * VM collocation only!
 * @property integer $vm_id
 * @property TariffVm $vm
 *
 * @property TariffVoipGroup $voipGroup
 * @property TariffVoipCity[] $voipCities
 * @property boolean $isTest
 */
class Tariff extends HistoryActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    // Методы для полей insert_time, insert_user_id, update_time, update_user_id
    use \app\classes\traits\InsertUpdateUserTrait;

    const DELTA = 10000;

    const NUMBER_TYPE_NUMBER = 'number';
    const NUMBER_TYPE_7800 = '7800';
    const NUMBER_TYPE_LINE = 'line';

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::className(),
        ];

    }

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
                    'count_of_validity_period',
                    'tariff_person_id',
                    'is_autoprolongation',
                    'is_include_vat',
                    'is_charge_after_blocking',
                    'is_default',
                    'is_postpaid',
                    'country_id',
                    'vm_id',
                ],
                'integer'
            ],
            [['voip_group_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['currency_id'], 'string', 'max' => 3],
            [['name'], 'required'],
            ['vm_id', 'validatorVm', 'skipOnEmpty' => false],
            ['count_of_validity_period', 'validatorTest', 'skipOnEmpty' => false],
        ];
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return self::getUrlById($this->id);
    }

    /**
     * @param int $id
     * @return string
     */
    public static function getUrlById($id)
    {
        return Url::to(['/uu/tariff/edit', 'id' => $id]);
    }

    /**
     * @param int $serviceTypeId
     * @return string
     */
    public static function getUrlNew($serviceTypeId)
    {
        return Url::to(['/uu/tariff/new', 'serviceTypeId' => $serviceTypeId]);
    }

    /**
     * @return ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['id' => 'currency_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPackage()
    {
        return $this->hasOne(Package::className(), ['tariff_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPackageMinutes()
    {
        return $this->hasMany(PackageMinute::className(), ['tariff_id' => 'id'])
            ->indexBy('id');
    }

    /**
     * @return ActiveQuery
     */
    public function getPackagePrices()
    {
        return $this->hasMany(PackagePrice::className(), ['tariff_id' => 'id'])
            ->indexBy('id');
    }

    /**
     * @return ActiveQuery
     */
    public function getPackagePricelists()
    {
        return $this->hasMany(PackagePricelist::className(), ['tariff_id' => 'id'])
            ->indexBy('id');
    }

    /**
     * @param int $resourceId
     * @return ActiveQuery
     */
    public function getTariffResource($resourceId)
    {
        return $this->getTariffResources()
            ->where(['resource_id' => $resourceId]);
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
    public function getTariffResourcesIndexedByResourceId()
    {
        return $this->getTariffResources()
            ->indexBy('resource_id');
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
    public function getPerson()
    {
        return $this->hasOne(TariffPerson::className(), ['id' => 'tariff_person_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getVm()
    {
        return $this->hasOne(TariffVm::className(), ['id' => 'vm_id']);
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
     * Есть ли услуги
     *
     * @return bool
     */
    public function isHasAccountTariff()
    {
        $tariffPeriods = $this->tariffPeriods;
        foreach ($tariffPeriods as $tariffPeriod) {
            if ($tariffPeriod->getAccountTariffs()->count()) {
                return true;
            }
        }

        return false;
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
            // 'operator' => 'Оператор',
        ];

        if ($isWithEmpty) {
            $types = (['' => '----'] + $types);
        }

        if ($countryId && $countryId != Country::RUSSIA) {
            unset($types[self::NUMBER_TYPE_7800]); // 7800 только в России
        }

        if ($countryId && $countryId == Country::RUSSIA) {
            unset($types[self::NUMBER_TYPE_LINE]); // линия без номера только не в России
        }

        return $types;
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @param int $serviceTypeId
     * @return string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false,
        $serviceTypeId = null
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['name' => SORT_ASC],
            $where = ($serviceTypeId ? ['service_type_id' => $serviceTypeId] : [])
        );
    }

    /**
     * Тестовый ли?
     *
     * @return bool
     */
    public function getIsTest()
    {
        return $this->tariff_status_id == TariffStatus::ID_TEST ||
            $this->tariff_status_id == TariffStatus::ID_VOIP_8800_TEST;

    }

    /**
     * VM должен быть заполнен
     *
     * @param string $attribute
     * @param array $params
     */
    public function validatorVm($attribute, $params)
    {
        if ($this->service_type_id == ServiceType::ID_VM_COLLOCATION && !$this->vm_id) {
            $this->addError($attribute, 'Необходимо указать тариф VM collocation');
            return;
        }
    }

    /**
     * Тестовый тариф должен быть без автопролонгации
     *
     * @param string $attribute
     * @param array $params
     */
    public function validatorTest($attribute, $params)
    {
        if ($this->getIsTest() && ($this->is_autoprolongation || !$this->count_of_validity_period)) {
            $this->addError($attribute, 'У тестового тарифа должно быть явно указано количество дней и не должно быть автопролонгации.');
            return;
        }
    }

    /**
     * Найти и вернуть дефолтный пакет
     *
     * @param int $cityId
     * @param int[] $tariffStatuses
     * @return Tariff[]|null
     */
    public function findDefaultPackages($cityId, $tariffStatuses = [])
    {
        if ($this->service_type_id != ServiceType::ID_VOIP) {
            return null;
        }

        $tariffTableName = Tariff::tableName();
        return Tariff::find()
            ->joinWith('voipCities')
            ->where([
                $tariffTableName . '.service_type_id' => ServiceType::ID_VOIP_PACKAGE,
                $tariffTableName . '.currency_id' => $this->currency_id,
                $tariffTableName . '.is_postpaid' => $this->is_postpaid,
                $tariffTableName . '.is_default' => 1,
                $tariffTableName . '.tariff_status_id' => $tariffStatuses,
                TariffVoipCity::tableName() . '.city_id' => $cityId,
            ])
            ->all();
    }
}