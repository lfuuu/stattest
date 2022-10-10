<?php

namespace app\modules\uu\models;

use app\classes\Html;
use app\classes\model\ActiveRecord;
use app\classes\traits\GetInsertUserTrait;
use app\classes\traits\GetUpdateUserTrait;
use app\models\Currency;
use app\models\Organization;
use app\modules\nnp\models\Package;
use app\modules\nnp\models\PackageApi;
use app\modules\nnp\models\PackageMinute;
use app\modules\nnp\models\PackagePrice;
use app\modules\nnp\models\PackagePricelist;
use app\modules\nnp\models\PackagePricelistNnp;
use app\modules\nnp\models\PackagePricelistNnpInternet;
use app\modules\nnp\models\PackagePricelistNnpSms;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Expression;
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
 * @property integer $tariff_person_id
 * @property integer $is_autoprolongation
 * @property integer $is_charge_after_blocking
 * @property integer $is_include_vat
 * @property integer $is_default
 * @property integer $tag_id
 * @property integer $count_of_carry_period
 * @property integer $is_one_active
 * @property integer $is_proportionately
 * @property integer $tax_rate
 * @property integer $is_bundle
 * @property integer $is_one_alt
 *
 * @property-read Currency $currency
 * @property-read TariffResource[] $tariffResources
 * @property-read TariffResource[] $tariffResourcesIndexedByResourceId
 * @property-read ServiceType $serviceType
 * @property-read TariffStatus $status
 * @property-read TariffPerson $person
 * @property-read TariffPeriod[] $tariffPeriods
 * @property-read Tag $tag
 * @property-read TariffTag[] $tariffTags
 * @property-read TariffBundle[] $bundleTariffs
 * @property-read TariffBundle[] $bundlePackages
 *
 * VOIP && VOIP package only!
 * @property integer $voip_group_id
 *
 * VOIP package only!
 * @property-read Package $package
 * @property-read PackageMinute[] $packageMinutes
 * @property-read PackagePrice[] $packagePrices
 * @property-read PackagePricelist[] $packagePricelists
 * @property-read PackagePricelistNnp[] $packagePricelistsNnp
 * @property-read PackagePricelistNnpInternet[] $packagePricelistsNnpInternet
 * @property-read PackagePricelistNnpSms[] $packagePricelistsNnpSms
 *
 * billing API
 * @property-read PackageApi[] $packageApi
 *
 * VPS only!
 * @property integer $vm_id
 * @property-read TariffVm $vm
 *
 * @property-read TariffCountry[] $tariffCountries
 * @property-read TariffVoipCountry[] $tariffVoipCountries
 * @property-read TariffVoipGroup $voipGroup
 * @property-read TariffVoipCity[] $voipCities
 * @property-read TariffOrganization[] $organizations
 * @property-read TariffTags[] $tags
 * @property-read TariffVoipNdcType[] $voipNdcTypes
 * @property-read boolean $isTest
 *
 * @method static Tariff findOne($condition)
 * @method static Tariff[] findAll($condition)
 */
class Tariff extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    use GetInsertUserTrait;
    use GetUpdateUserTrait;

    const DELTA = 10000;

    const TEST_VOIP_ID = 10005;
    const TEST_VPBX_ID = 10123;
    const START_VPBX_ID = 10143;
    const AUTODIAL_IDS = [13167, 13509];

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                \app\classes\behaviors\HistoryChanges::class,
                [
                    // Установить "когда создал" и "когда обновил"
                    'class' => TimestampBehavior::class,
                    'createdAtAttribute' => 'insert_time',
                    'updatedAtAttribute' => 'update_time',
                    'value' => new Expression('UTC_TIMESTAMP()'), // "NOW() AT TIME ZONE 'utc'" (PostgreSQL) или 'UTC_TIMESTAMP()' (MySQL)
                ],
                [
                    // Установить "кто создал" и "кто обновил"
                    'class' => AttributeBehavior::class,
                    'attributes' => [
                        ActiveRecord::EVENT_BEFORE_INSERT => ['insert_user_id', 'update_user_id'],
                        ActiveRecord::EVENT_BEFORE_UPDATE => 'update_user_id',
                    ],
                    'value' => Yii::$app->user->getId(),
                ],
            ]
        );
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
                    'tariff_person_id',
                    'is_autoprolongation',
                    'is_include_vat',
                    'is_charge_after_blocking',
                    'is_default',
                    'is_bundle',
                    'count_of_carry_period',
                    'vm_id',
                    'tag_id',
                    'is_one_active',
                    'is_proportionately',
                    'tax_rate',
                    'is_one_alt',

                ],
                'integer'
            ],
            ['count_of_carry_period', 'default', 'value' => 0],
            [['voip_group_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['currency_id'], 'string', 'max' => 3],
            [['name'], 'required'],
            ['vm_id', 'validatorVm', 'skipOnEmpty' => false],
            [['count_of_validity_period', 'count_of_carry_period'], 'integer', 'min' => 0, 'max' => 365],
            ['count_of_validity_period', 'validatorTest', 'skipOnEmpty' => false],
            ['count_of_carry_period', 'validatorBurnInternet'],
            [['overview'], 'string'],
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
     * Вернуть html: имя + ссылка
     *
     * @return string
     */
    public function getLink()
    {
        return Html::a(
            Html::encode($this->name),
            $this->getUrl()
        );
    }

    /**
     * @return ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPackage()
    {
        return $this->hasOne(Package::class, ['tariff_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPackageMinutes()
    {
        return $this->hasMany(PackageMinute::class, ['tariff_id' => 'id'])
            ->indexBy('id');
    }

    /**
     * @return ActiveQuery
     */
    public function getPackagePrices()
    {
        return $this->hasMany(PackagePrice::class, ['tariff_id' => 'id'])
            ->orderBy(['weight' => SORT_DESC])
            ->indexBy('id');
    }

    /**
     * @return ActiveQuery
     */
    public function getPackagePricelists()
    {
        return $this->hasMany(PackagePricelist::class, ['tariff_id' => 'id'])
            ->andWhere(['nnp_pricelist_id' => null])
            ->indexBy('id');
    }

    /**
     * @return ActiveQuery
     */
    public function getPackagePricelistsNnp()
    {
        return $this->hasMany(PackagePricelistNnp::class, ['tariff_id' => 'id'])
            ->andWhere(['pricelist_id' => null])
            ->indexBy('id');
    }

    /**
     * @return ActiveQuery
     */
    public function getPackagePricelistsNnpInternet()
    {
        return $this->hasMany(PackagePricelistNnpInternet::class, ['tariff_id' => 'id'])
            ->indexBy('id');
    }

    /**
     * @return ActiveQuery
     */
    public function getPackagePricelistsNnpSms()
    {
        return $this->hasMany(PackagePricelistNnpSms::class, ['tariff_id' => 'id'])
            ->indexBy('id');
    }

    /**
     * @return ActiveQuery
     */
    public function getPackageApi()
    {
        return $this->hasMany(PackageApi::class, ['tariff_id' => 'id']);
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
        return $this->hasMany(TariffResource::class, ['tariff_id' => 'id'])
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
        return $this->hasMany(TariffPeriod::class, ['tariff_id' => 'id'])
            ->indexBy('id');
    }

    /**
     * @return ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(TariffStatus::class, ['id' => 'tariff_status_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPerson()
    {
        return $this->hasOne(TariffPerson::class, ['id' => 'tariff_person_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getVm()
    {
        return $this->hasOne(TariffVm::class, ['id' => 'vm_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getServiceType()
    {
        return $this->hasOne(ServiceType::class, ['id' => 'service_type_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getTag()
    {
        return $this->hasOne(Tag::class, ['id' => 'tag_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getTariffTags()
    {
        return $this->hasMany(TariffTags::class, ['tariff_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getVoipGroup()
    {
        return $this->hasOne(TariffVoipGroup::class, ['id' => 'voip_group_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getVoipCities()
    {
        return $this->hasMany(TariffVoipCity::class, ['tariff_id' => 'id'])
            ->indexBy('city_id');
    }

    /**
     * @return ActiveQuery
     */
    public function getTariffCountries()
    {
        return $this->hasMany(TariffCountry::class, ['tariff_id' => 'id'])
            ->indexBy('country_id');
    }

    /**
     * @return ActiveQuery
     */
    public function getTariffVoipCountries()
    {
        return $this->hasMany(TariffVoipCountry::class, ['tariff_id' => 'id'])
            ->indexBy('country_id');
    }

    /**
     * @return ActiveQuery
     */
    public function getVoipNdcTypes()
    {
        return $this->hasMany(TariffVoipNdcType::class, ['tariff_id' => 'id'])
            ->indexBy('ndc_type_id');
    }

    /**
     * @return ActiveQuery
     */
    public function getOrganizations()
    {
        return $this->hasMany(TariffOrganization::class, ['tariff_id' => 'id'])
            ->orderBy([TariffOrganization::tableName() . '.id' => SORT_DESC])
            ->indexBy('organization_id');
    }

    /**
     * @return ActiveQuery
     */
    public function getTags()
    {
        return $this->hasMany(TariffTags::class, ['tariff_id' => 'id'])
            ->orderBy(['id' => SORT_DESC])
            ->indexBy('tag_id');
    }


    /**
     * @return ActiveQuery
     */
    public function getBundlePackages()
    {
        return $this->hasMany(TariffBundle::class, ['tariff_id' => 'id'])
            ->orderBy(['package_tariff_id' => SORT_ASC]);
    }

    /**
     * @return ActiveQuery
     */
    public function getBundleTariffs()
    {
        return $this->hasMany(TariffBundle::class, ['package_tariff_id' => 'id'])
            ->orderBy(['tariff_id' => SORT_ASC]);
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
            if ($tariffPeriod->getAccountTariffLogs()->count()) {
                return true;
            }
        }

        return false;
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
        $serviceTypeId = null,
        $_where = []
    )
    {
        $where = ($serviceTypeId ? ['service_type_id' => $serviceTypeId] : []);
        if ($_where) {
            $where = ['AND', $where, $_where];
        }
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['name' => SORT_ASC],
            $where
        );
    }

    /**
     * Тестовый ли?
     *
     * @return bool
     */
    public function getIsTest()
    {
        return array_key_exists($this->tariff_status_id, self::getTestStatuses());

    }

    /**
     * Дефолтный пакет?
     *
     * @return bool
     */
    public function getIsDefaultPackage()
    {
        return $this->is_default && array_key_exists($this->service_type_id, ServiceType::$packages);

    }

    /**
     * Тестовые статусы
     *
     * @return int[]
     */
    public static function getTestStatuses()
    {
        return [
            TariffStatus::ID_TEST => TariffStatus::ID_TEST,
            TariffStatus::ID_VOIP_8800_TEST => TariffStatus::ID_VOIP_8800_TEST,
        ];

    }

    /**
     * VPS должен быть заполнен
     *
     * @param string $attribute
     * @param array $params
     */
    public function validatorVm($attribute, $params)
    {
        if ($this->service_type_id == ServiceType::ID_VPS && !$this->vm_id) {
            $this->addError($attribute, 'Необходимо указать тариф VPS');
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
     * Несгорающий пакет интернета должен быть одноразовым
     *
     * @param string $attribute
     * @param array $params
     */
    public function validatorBurnInternet($attribute, $params)
    {
        if ($this->service_type_id == ServiceType::ID_VOIP_PACKAGE_INTERNET && $this->count_of_carry_period && ($this->is_autoprolongation || $this->count_of_validity_period)) {
            $this->addError($attribute, 'Несгорающий пакет интернета должен быть одноразовым (Автопролонгация = нет, Кол-во продлений = 0)');
            return;
        }
    }

    /**
     * Найти и вернуть дефолтные пакеты
     *
     * @param int $countryId
     * @param int $cityId
     * @param int $voipCountryId
     * @param int $ndcTypeId
     * @param bool $isIncludeVat
     * @param int[] $tariffStatuses
     * @param int $packageType
     * @param int $organizationId
     * @return Tariff[]|null
     */
    public function findDefaultPackages($countryId, $cityId, $voipCountryId, $ndcTypeId, $isIncludeVat, $tariffStatuses, $packageType, $organizationId)
    {
        if ($this->service_type_id == ServiceType::ID_VOIP && !$ndcTypeId) {
            // пакеты по умолчанию только для телефонии и билнгации API. Даже для пакетов транков их нет
            return null;
        }

        $tariffTableName = self::tableName();
        $query = self::find()
            ->joinWith('tariffCountries')
            ->where([
                $tariffTableName . '.service_type_id' => $packageType,
                TariffCountry::tableName() . '.country_id' => $countryId,
                $tariffTableName . '.currency_id' => $this->currency_id,
                $tariffTableName . '.is_default' => 1,
                $tariffTableName . '.tariff_status_id' => $tariffStatuses,
                $tariffTableName . '.is_include_vat' => $isIncludeVat,
            ]);

        if ($this->service_type_id == ServiceType::ID_VOIP) {
            if ($cityId) {
                $query->joinWith('voipCities')
                    ->andWhere(['OR',
                        [TariffVoipCity::tableName() . '.city_id' => $cityId],
                        [TariffVoipCity::tableName() . '.city_id' => null]
                    ]);
            }

            if ($voipCountryId) {
                $query->joinWith('tariffVoipCountries')
                    ->andWhere(['OR',
                        [TariffVoipCountry::tableName() . '.country_id' => $voipCountryId],
                        [TariffVoipCountry::tableName() . '.country_id' => null]
                    ]);
            }

            if ($ndcTypeId) {
                $query->joinWith('voipNdcTypes')
                    ->andWhere(['OR',
                        [TariffVoipNdcType::tableName() . '.ndc_type_id' => $ndcTypeId],
                        [TariffVoipNdcType::tableName() . '.ndc_type_id' => null],
                    ]);
            }
        }

        if ($organizationId) {
            $query->joinWith('organizations')
                ->andWhere([TariffOrganization::tableName() . '.organization_id' => $organizationId]);
        }

        return $query->all();
    }

    /**
     * Подготовка полей для исторических данных
     *
     * @param string $field
     * @param string $value
     * @return string
     */
    public static function prepareHistoryValue($field, $value)
    {
        switch ($field) {

            case 'tariff_status_id':
                if ($tariffStatus = TariffStatus::findOne(['id' => $value])) {
                    return $tariffStatus->name;
                }
                break;

            case 'tariff_person_id':
                if ($tariffPerson = TariffPerson::findOne(['id' => $value])) {
                    return $tariffPerson->name;
                }
                break;

            case 'tag_id':
                if ($tariffTag = Tag::findOne(['id' => $value])) {
                    return $tariffTag->name;
                }
                break;
        }

        return $value;
    }

    /**
     * Какие поля не показывать в исторических данных
     *
     * @param string $action
     * @return string[]
     */
    public static function getHistoryHiddenFields($action)
    {
        return [
            'id',
            'insert_user_id',
            'update_user_id',
            'insert_time',
            'update_time',
            'service_type_id',
        ];
    }

    /**
     * Вернуть строку с организациями для текстового отображения
     *
     * @return string
     */
    public function getOrganizationsText()
    {
        $organizations = $this->organizations;
        return implode(', ', $organizations);
    }

    /**
     * Вернуть строку со странами для текстового отображения
     *
     * @return string
     */
    public function getCountriesText()
    {
        $countries = $this->tariffCountries;
        return implode(', ', $countries);
    }

    /**
     * Вернуть строку с организациями для отображения в ячейке грида
     *
     * @param int $maxCount
     * @return string
     */
    public function getOrganizationsString($maxCount = 2)
    {
        $organizations = $this->organizations;
        $count = count($organizations);
        if ($count <= $maxCount) {
            return implode('<br/>', $organizations);
        }

        $maxCount--;

        return sprintf(
            '%s<br/><abbr title="%s">… %d…</abbr>',
            implode('<br/>', array_slice($organizations, 0, $maxCount)),
            implode(PHP_EOL, array_slice($organizations, $maxCount)),
            $count - $maxCount
        );
    }

    /**
     * @return array
     */
    public static function getHelpConfluence()
    {
        return ['confluenceId' => 25887484, 'message' => 'Тариф'];
    }

    /**
     * Тариф для автодиала
     * @return bool
     */
    public function isAutodial()
    {
        return in_array($this->id, self::AUTODIAL_IDS);
    }
}