<?php

namespace app\modules\uu\models;

use app\classes\model\HistoryActiveRecord;
use Yii;
use yii\db\ActiveQuery;

/**
 * Стоимость тарифа
 *
 * @property integer $id
 * @property float $price_per_period
 * @property float $price_setup
 * @property float $price_min
 * @property int $tariff_id
 * @property int $charge_period_id
 *
 * @property Period $chargePeriod
 * @property Tariff $tariff
 * @property AccountTariff[] $accountTariffs
 */
class TariffPeriod extends HistoryActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    const IS_NOT_SET = -1;
    const IS_SET = -2;

    const TEST_VOIP_ID = 5399;
    const TEST_VPBX_ID = 5670;
    const START_VPBX_ID = 5694;

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
        return parent::behaviors() + [
                'HistoryChanges' => \app\classes\behaviors\HistoryChanges::className(),
            ];

    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['price_per_period', 'price_min', 'price_setup'], 'required'],
            [['charge_period_id'], 'required'],
            [['price_per_period', 'price_min', 'price_setup'], 'number'],
            ['charge_period_id', 'validatorPeriod'],
        ];
    }

    /**
     * @return string
     */
    public function getName()
    {
        $tariff = $this->tariff;
        return sprintf(
            '%s %d %s %s',
            $tariff->name,
            $this->price_per_period,
            $tariff->currency->symbol,
            $this->chargePeriod->name
        );
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
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return ActiveQuery
     */
    public function getChargePeriod()
    {
        return $this->hasOne(Period::className(), ['id' => 'charge_period_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getTariff()
    {
        return $this->hasOne(Tariff::className(), ['id' => 'tariff_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountTariffs()
    {
        return $this->hasMany(AccountTariff::className(), ['tariff_period_id' => 'id'])
            ->indexBy('id');
    }

    /**
     * @param int $defaultTariffPeriodId
     * @param int $serviceTypeId
     * @param int $currency
     * @param int $countryId
     * @param int $cityId
     * @param bool $isWithEmpty
     * @param bool $isWithNullAndNotNull
     * @param int $statusId
     * @param bool $isPostpaid
     * @param bool $isIncludeVat
     * @param int $organizationId
     * @return array
     */
    public static function getList(
        &$defaultTariffPeriodId,
        $serviceTypeId,
        $currency = null,
        $countryId = null,
        $cityId = null,
        $isWithEmpty = false,
        $isWithNullAndNotNull = false,
        $statusId = null,
        $isPostpaid = null,
        $isIncludeVat = null,
        $organizationId = null
    ) {
        $defaultTariffPeriodId = null;

        $activeQuery = self::find()
            ->innerJoinWith('tariff tariff')
            ->andWhere(['tariff.service_type_id' => $serviceTypeId])
            ->orderBy(['tariff.tariff_status_id' => SORT_ASC]);

        if ($currency) {
            $activeQuery->andWhere(['tariff.currency_id' => $currency]);
        }

        if (!is_null($isPostpaid)) {
            $activeQuery->andWhere(['tariff.is_postpaid' => $isPostpaid]);
        }

        if ($statusId) {
            $activeQuery->andWhere(['tariff.tariff_status_id' => $statusId]);
        }

        if ($isIncludeVat) {
            $activeQuery->andWhere(['tariff.is_include_vat' => $isIncludeVat]);
        }

        if ($countryId) {
            $activeQuery->andWhere(['tariff.country_id' => $countryId]);
        }

        if ($cityId && isset(ServiceType::$packages[$serviceTypeId])) {
            $activeQuery
                ->innerJoin(TariffVoipCity::tableName() . ' tariff_cities', 'tariff.id = tariff_cities.tariff_id')
                ->andWhere(['tariff_cities.city_id' => $cityId]);
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

        /** @var TariffPeriod $tariffPeriod */
        foreach ($activeQuery->each() as $tariffPeriod) {
            $status = $tariffPeriod->tariff->status->name; // @todo надо бы заджойнить таблицу status
            if ($tariffPeriod->tariff->is_default) {
                $defaultTariffPeriodId = $tariffPeriod->id;
            }

            if (!isset($selectboxItems[$status])) {
                $selectboxItems[$status] = [];
            }

            $selectboxItems[$status][$tariffPeriod->id] = $tariffPeriod->getName();
        }

        return $selectboxItems;
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
            ($tariff->is_postpaid || array_key_exists($tariff->service_type_id, ServiceType::$packages))
            && $this->charge_period_id != Period::ID_MONTH
        ) {
            $this->addError($attribute, 'У постоплаты и пакетов может быть только помесячное списание');
            return;
        }

        if ($tariff->isTest && $this->charge_period_id != Period::ID_DAY) {
            $this->addError($attribute, 'У тестовых тарифов может быть только посуточное списание');
            return;
        }
    }
}
