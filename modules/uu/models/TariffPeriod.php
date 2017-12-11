<?php

namespace app\modules\uu\models;

use app\classes\Html;
use app\classes\model\ActiveRecord;
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
 * @property-read Period $chargePeriod
 * @property-read Tariff $tariff
 * @property-read AccountTariff[] $accountTariffs
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
     * @param int $ndcTypeId
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
        $organizationId = null,
        $ndcTypeId = null
    ) {
        $defaultTariffPeriodId = null;

        if ($serviceTypeId == ServiceType::ID_VOIP_PACKAGE_INTERNET) {

            // пакеты интернета - по стране
            $organizationId = null;
            $cityId = null;

        } elseif ($serviceTypeId == ServiceType::ID_VOIP_PACKAGE_CALLS) {

            // пакеты телефонии - по стране
            $organizationId = null;

        } else {

            // все остальное - по организации
            $countryId = null;
        }

        $activeQuery = self::find()
            ->innerJoinWith('tariff tariff')
            ->andWhere(['tariff.service_type_id' => $serviceTypeId])
            ->orderBy(['tariff.tariff_status_id' => SORT_ASC]);

        if ($currency) {
            $activeQuery->andWhere(['tariff.currency_id' => $currency]);
        }

        if (array_key_exists($serviceTypeId, ServiceType::$packages)) {
            // для пакетов не делить prepaid/postpaid
            $isPostpaid = null;
        }

        if (!is_null($isPostpaid)) {
            $activeQuery->andWhere(['tariff.is_postpaid' => $isPostpaid]);
        }

        if ($statusId) {
            $activeQuery->andWhere(['tariff.tariff_status_id' => $statusId]);
        }

        if (!is_null($isIncludeVat)) {
            $activeQuery->andWhere(['tariff.is_include_vat' => $isIncludeVat]);
        }

        if ($countryId) {
            $activeQuery->andWhere(['tariff.country_id' => $countryId]);
        }

        if ($cityId && in_array($serviceTypeId, [ServiceType::ID_VOIP, ServiceType::ID_VOIP_PACKAGE_CALLS])) {
            $activeQuery
                ->innerJoin(TariffVoipCity::tableName() . ' tariff_cities', 'tariff.id = tariff_cities.tariff_id')
                ->andWhere(['tariff_cities.city_id' => $cityId]);
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

        /** @var TariffPeriod $tariffPeriod */
        foreach ($activeQuery->each() as $tariffPeriod) {

            $tariff = $tariffPeriod->tariff;
            $status = $tariff->status; // @todo надо бы заджойнить таблицу status

            if ($tariff->is_default && !$defaultTariffPeriodId && $status->id != TariffStatus::ID_ARCHIVE) {
                $defaultTariffPeriodId = $tariffPeriod->id;
            }

            if (!isset($selectboxItems[$status->name])) {
                $selectboxItems[$status->name] = [];
            }

            $selectboxItems[$status->name][$tariffPeriod->id] = (($status->id == TariffStatus::ID_PUBLIC) ? '' : $status->name . '. ') . $tariffPeriod->getName();
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
