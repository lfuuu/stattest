<?php

namespace app\modules\uu\models;

use app\classes\helpers\DependecyHelper;
use app\classes\Html;
use app\classes\model\ActiveRecord;
use Yii;
use yii\caching\ChainedDependency;
use yii\caching\DbDependency;
use yii\caching\TagDependency;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * Стоимость тарифа
 *
 * @property integer $id
 * @property float $price_per_period
 * @property float $price_setup
 * @property integer $price_min
 * @property int $tariff_id
 * @property int $charge_period_id
 *
 * @property-read Period $chargePeriod
 * @property-read Tariff $tariff
 * @property-read AccountTariff[] $accountTariffs
 * @property-read AccountTariffLog[] $accountTariffLogs
 *
 * @method static TariffPeriod findOne($condition)
 * @method static TariffPeriod[] findAll($condition)
 */
class TariffPeriod extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    const IS_NOT_SET = -1;
    const IS_SET = -2;

    const TEST_VOIP_ID = 5399;
    const TEST_VPBX_ID = 5670;
    const START_VPBX_ID = 5694;

    const PORTED_ID = 9665;

    // size for getList()
    const BATCH_SIZE_READ = 500;

    protected $isAttributeTypecastBehavior = true;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_tariff_period';
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                \app\classes\behaviors\HistoryChanges::class,
            ]
        );

    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['price_per_period', 'price_min', 'price_setup'], 'required'],
            [['charge_period_id'], 'required'],
            [['price_per_period', 'price_setup'], 'number'],
            ['charge_period_id', 'validatorPeriod'],
            ['price_min', 'integer'],
        ];
    }

    /**
     * @return string
     */
    public function getName()
    {
        $tariff = $this->tariff;
        return sprintf(
            '%s %s %s %s',
            $tariff->name,
            $this->price_per_period,
            $tariff->currency->symbol,
            $this->chargePeriod->name
        );
    }

    /**
     * @return string
     */
    public function getNameWithTariffId()
    {
        $tariff = $this->tariff;
        return Yii::t('models/' . self::tableName(), 'name_with_id', [
            'name' => $tariff->name,
            'price' => $this->price_per_period,
            'currency' => $tariff->currency->symbol,
            'periodName' => $this->chargePeriod->name,
            'tariffId' => $tariff->id
        ]);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->tariff->getUrl();
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return Html::a(
            Html::encode($this->getName()),
            $this->getUrl()
        );
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return ActiveQuery
     */
    public function getChargePeriod()
    {
        return $this->hasOne(Period::class, ['id' => 'charge_period_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getTariff()
    {
        return $this->hasOne(Tariff::class, ['id' => 'tariff_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountTariffs()
    {
        return $this->hasMany(AccountTariff::class, ['tariff_period_id' => 'id'])
            ->indexBy('id');
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountTariffLogs()
    {
        return $this->hasMany(AccountTariffLog::class, ['tariff_period_id' => 'id'])
            ->indexBy('id');
    }

    /**
     * @param int $defaultTariffPeriodId
     * @param int $serviceTypeId
     * @param int $currency
     * @param int $countryId
     * @param int $voipCountryId
     * @param int $cityId
     * @param bool $isWithEmpty
     * @param bool $isWithNullAndNotNull
     * @param int $statusId
     * @param bool $isIncludeVat
     * @param int $organizationId
     * @param int $ndcTypeId
     * @param bool $withTariffId
     * @return array
     */
    public static function getList(
        &$defaultTariffPeriodId,
        $serviceTypeId,
        $currency = null,
        $countryId = null,
        $voipCountryId = null,
        $cityId = null,
        $isWithEmpty = false,
        $isWithNullAndNotNull = false,
        $statusId = null,
        $isIncludeVat = null,
        $organizationId = null,
        $ndcTypeId = null,
        $withTariffId = false
    )
    {
        if ($serviceTypeId == ServiceType::ID_VOIP_PACKAGE_INTERNET) {

            // пакеты интернета - по стране
            $organizationId = null;
            $cityId = null;

        } elseif (in_array($serviceTypeId, [ServiceType::ID_VOIP, ServiceType::ID_VOIP_PACKAGE_CALLS])) {

            // телефония и ее пакеты - по стране
            $organizationId = null;
        }

        $activeQuery = self::find()
            ->alias('tp')
            ->innerJoinWith('tariff tariff')
            ->with('tariff.status')
            ->with('tariff.currency')
            ->with('chargePeriod')
            ->andWhere(['tariff.service_type_id' => $serviceTypeId])
            ->orderBy(['tariff.tariff_status_id' => SORT_ASC]);

        if ($currency) {
            $activeQuery->andWhere(['tariff.currency_id' => $currency]);
        }

        if ($statusId) {
            $activeQuery->andWhere(['tariff.tariff_status_id' => $statusId]);
        }

        if (!is_null($isIncludeVat)) {
            $activeQuery->andWhere(['tariff.is_include_vat' => $isIncludeVat]);
        }

        if ($countryId) {
            $activeQuery
                ->innerJoin(TariffCountry::tableName() . ' as tariff_country', 'tariff.id = tariff_country.tariff_id')
                ->andWhere(['tariff_country.country_id' => $countryId]);
        }

        if ($voipCountryId) {
            $activeQuery
                ->innerJoin(TariffVoipCountry::tableName() . ' as tariff_voip_country', 'tariff.id = tariff_voip_country.tariff_id')
                ->andWhere(['tariff_voip_country.country_id' => $voipCountryId]);
        }

        if ($cityId && in_array($serviceTypeId, [ServiceType::ID_VOIP, ServiceType::ID_VOIP_PACKAGE_CALLS])) {
            $activeQuery
                ->leftJoin(TariffVoipCity::tableName() . ' tariff_cities', 'tariff.id = tariff_cities.tariff_id')
                ->andWhere([
                    'OR',
                    ['tariff_cities.city_id' => $cityId], // если в тарифе хоть один город, то надо только точное соотвествие
                    ['tariff_cities.city_id' => null] // если в тарифе ни одного города нет, то это означает "любой город этой страны"
                ]);
        }

        if ($ndcTypeId && in_array($serviceTypeId, [ServiceType::ID_VOIP, ServiceType::ID_VOIP_PACKAGE_CALLS])) {
            $activeQuery
                ->innerJoin(TariffVoipNdcType::tableName() . ' tariff_ndc_type', 'tariff.id = tariff_ndc_type.tariff_id')
                ->andWhere(['tariff_ndc_type.ndc_type_id' => $ndcTypeId]);
        }

        if ($organizationId) {
            $activeQuery
                ->innerJoin(TariffOrganization::tableName() . ' tariff_organizations', 'tariff.id = tariff_organizations.tariff_id')
                ->andWhere(['tariff_organizations.organization_id' => $organizationId]);
        }

        $selectboxItems = [];

        if ($isWithEmpty) {
            $selectboxItems[''] = '----';
        }

        if ($isWithNullAndNotNull) {
            $selectboxItems[self::IS_NOT_SET] = Yii::t('common', 'Switched off');
            $selectboxItems[self::IS_SET] = Yii::t('common', 'Switched on');
        }


        $checkCacheQuery1 = clone $activeQuery;

        $key = self::tableName() . '_getList_' . md5($checkCacheQuery1->createCommand()->rawSql . implode(',', array_values($selectboxItems)));

        $tpFields = \Yii::$app->cache->getOrSet('firstTariffPeriod', function () {
            return array_keys(TariffPeriod::find()->one()->getAttributes());
        }, 3600 * 24, (new TagDependency(['tags' => [DependecyHelper::TAG_UU_SERVICE_LIST]])));

        $tFields = \Yii::$app->cache->getOrSet('firstTariff', function () {
            return array_keys(Tariff::find()->one()->getAttributes());
        }, 3600 * 24, (new TagDependency(['tags' => [DependecyHelper::TAG_UU_SERVICE_LIST]])));

        $checkCacheQuery2 = clone $activeQuery;

        $sqlDep = $checkCacheQuery2->select([
            'sum' => new Expression('sum(tp.' . implode('+tp.', $tpFields) . ')'),
            'name' => new Expression('md5(group_concat(tariff.name))'),
            'tariff_options' => new Expression('md5(group_concat(tariff.' . implode('+tariff.', $tFields) . '))'),
        ])->createCommand()->rawSql;

        $dbDep = new DbDependency(['sql' => $sqlDep]);
        $tagDep = (new TagDependency(['tags' => [DependecyHelper::TAG_UU_SERVICE_LIST]]));
        $chainDep = (new ChainedDependency(['dependencies' => [$tagDep, $dbDep]]));

        return \Yii::$app->cache->getOrSet($key, function () use ($activeQuery, $selectboxItems, $withTariffId) {

            $defaultTariffPeriodId = null;

            /** @var TariffPeriod $tariffPeriod */
            foreach ($activeQuery->each(self::BATCH_SIZE_READ) as $tariffPeriod) {
                $tariff = $tariffPeriod->tariff;
                $status = $tariff->status; // @todo надо бы заджойнить таблицу status

                if ($tariff->is_default && !$defaultTariffPeriodId && $status->id != TariffStatus::ID_ARCHIVE) {
                    $defaultTariffPeriodId = $tariffPeriod->id;
                }

                if (!isset($selectboxItems[$status->name])) {
                    $selectboxItems[$status->name] = [];
                }

                $selectboxItems[$status->name][$tariffPeriod->id] =
                    (($status->id == TariffStatus::ID_PUBLIC) ? '' : $status->name . '. ') .
                    ($withTariffId ? $tariffPeriod->getNameWithTariffId() : $tariffPeriod->getName()) . ' №' . $tariffPeriod->tariff_id;
            }
            return $selectboxItems;
        }, 3600 * 24 * 30, $chainDep);

    }

    /**
     * У постоплаты и пакетов может быть только помесячное списание
     *
     * @param string $attribute
     * @param array $params
     */
    public function validatorPeriod($attribute, $params)
    {
        $tariff = $this->tariff;

        if (
            (array_key_exists($tariff->service_type_id, ServiceType::$packages))
            && $this->charge_period_id != Period::ID_MONTH
        ) {
            $this->addError($attribute, 'У пакетов может быть только помесячное списание');
            return;
        }

        if ($tariff->isTest && $this->charge_period_id != Period::ID_DAY) {
            $this->addError($attribute, 'У тестовых тарифов может быть только посуточное списание');
            return;
        }
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

            case 'charge_period_id':
                if ($period = Period::findOne(['id' => $value])) {
                    return $period->name;
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
            'tariff_id',
            'period_id', // это поле уже выпилено, но в истории осталось
        ];
    }

    /**
     * Вернуть ID родителя
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->tariff_id;
    }

    /**
     * Установить ID родителя
     *
     * @param int $parentId
     */
    public function setParentId($parentId)
    {
        $this->tariff_id = $parentId;
    }
}
