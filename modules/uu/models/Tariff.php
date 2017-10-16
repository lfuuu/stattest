<?php

namespace app\modules\uu\models;

use app\classes\Html;
use app\classes\model\HistoryActiveRecord;
use app\classes\traits\GetInsertUserTrait;
use app\classes\traits\GetUpdateUserTrait;
use app\models\Country;
use app\models\Currency;
use app\modules\nnp\models\Package;
use app\modules\nnp\models\PackageMinute;
use app\modules\nnp\models\PackagePrice;
use app\modules\nnp\models\PackagePricelist;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
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
 * @property integer $country_id
 * @property integer $tariff_person_id
 * @property integer $is_autoprolongation
 * @property integer $is_charge_after_blocking
 * @property integer $is_include_vat
 * @property integer $is_default
 * @property integer $is_postpaid
 * @property integer $tag_id
 *
 * @property-read Currency $currency
 * @property-read TariffResource[] $tariffResources
 * @property-read TariffResource[] $tariffResourcesIndexedByResourceId
 * @property-read ServiceType $serviceType
 * @property-read Country $country
 * @property-read TariffStatus $status
 * @property-read TariffPerson $person
 * @property-read TariffPeriod[] $tariffPeriods
 * @property-read TariffTag $tag
 *
 * VOIP && VOIP package only!
 * @property integer $voip_group_id
 *
 * VOIP package only!
 * @property-read Package $package
 * @property-read PackageMinute[] $packageMinutes
 * @property-read PackagePrice[] $packagePrices
 * @property-read PackagePricelist[] $packagePricelists
 *
 * VM collocation only!
 * @property integer $vm_id
 * @property-read TariffVm $vm
 *
 * @property-read TariffVoipGroup $voipGroup
 * @property-read TariffVoipCity[] $voipCities
 * @property-read TariffOrganization[] $organizations
 * @property-read TariffVoipCity[] $voipNdcTypes
 * @property-read boolean $isTest
 *
 * @method static Tariff findOne($condition)
 * @method static Tariff[] findAll($condition)
 */
class Tariff extends HistoryActiveRecord
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

    /**
     * @return array
     */
    public function behaviors()
    {
        return parent::behaviors() + [
                'HistoryChanges' => \app\classes\behaviors\HistoryChanges::className(),
                [
                    // Установить "когда создал" и "когда обновил"
                    'class' => TimestampBehavior::className(),
                    'createdAtAttribute' => 'insert_time',
                    'updatedAtAttribute' => 'update_time',
                    'value' => new Expression('UTC_TIMESTAMP()'), // "NOW() AT TIME ZONE 'utc'" (PostgreSQL) или 'UTC_TIMESTAMP()' (MySQL)
                ],
                [
                    // Установить "кто создал" и "кто обновил"
                    'class' => AttributeBehavior::className(),
                    'attributes' => [
                        ActiveRecord::EVENT_BEFORE_INSERT => ['insert_user_id', 'update_user_id'],
                        ActiveRecord::EVENT_BEFORE_UPDATE => 'update_user_id',
                    ],
                    'value' => Yii::$app->user->getId(),
                ],
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
                    'tariff_person_id',
                    'is_autoprolongation',
                    'is_include_vat',
                    'is_charge_after_blocking',
                    'is_default',
                    'is_postpaid',
                    'country_id',
                    'vm_id',
                    'tag_id',
                ],
                'integer'
            ],
            [['voip_group_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['currency_id'], 'string', 'max' => 3],
            [['name'], 'required'],
            ['vm_id', 'validatorVm', 'skipOnEmpty' => false],
            [['count_of_validity_period'], 'integer', 'max' => 30],
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
            ->orderBy(['weight' => SORT_DESC])
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
    public function getTag()
    {
        return $this->hasOne(TariffTag::className(), ['id' => 'tag_id']);
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
     * @return ActiveQuery
     */
    public function getVoipNdcTypes()
    {
        return $this->hasMany(TariffVoipNdcType::className(), ['tariff_id' => 'id'])
            ->indexBy('ndc_type_id');
    }

    /**
     * @return ActiveQuery
     */
    public function getOrganizations()
    {
        return $this->hasMany(TariffOrganization::className(), ['tariff_id' => 'id'])
            ->indexBy('organization_id');
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
        return array_key_exists($this->tariff_status_id, self::getTestStatuses());

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
     * Найти и вернуть дефолтные пакеты
     *
     * @param int $cityId
     * @param int $ndcTypeId
     * @param int[] $tariffStatuses
     * @return Tariff[]|null
     */
    public function findDefaultPackages($cityId, $ndcTypeId, $tariffStatuses = [])
    {
        if ($this->service_type_id != ServiceType::ID_VOIP || !$ndcTypeId) {
            // пакеты по умолчанию только для телефонии. Даже для пакетов транков их нет
            return null;
        }

        $tariffTableName = self::tableName();
        $query = self::find()
            ->where([
                $tariffTableName . '.service_type_id' => ServiceType::ID_VOIP_PACKAGE_CALLS,
                $tariffTableName . '.country_id' => $this->country_id,
                $tariffTableName . '.currency_id' => $this->currency_id,
                // $tariffTableName . '.is_postpaid' => $this->is_postpaid,
                $tariffTableName . '.is_default' => 1,
                $tariffTableName . '.tariff_status_id' => $tariffStatuses,
            ]);

        if ($cityId) {
            $query->joinWith('voipCities')
                ->andWhere([
                    TariffVoipCity::tableName() . '.city_id' => $cityId,
                ]);
        }

        if ($ndcTypeId) {
            $query->joinWith('voipNdcTypes')
                ->andWhere([
                    TariffVoipNdcType::tableName() . '.ndc_type_id' => $ndcTypeId,
                ]);
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

            case 'country_id':
                if ($country = Country::findOne(['code' => $value])) {
                    return $country->getLink();
                }
                break;

            case 'tag_id':
                if ($tariffTag = TariffTag::findOne(['id' => $value])) {
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
}