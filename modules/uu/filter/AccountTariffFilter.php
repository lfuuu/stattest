<?php

namespace app\modules\uu\filter;

use app\classes\traits\GetListTrait;
use app\models\Number;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffOrganization;
use app\modules\uu\models\TariffPeriod;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для AccountTariff
 */
class AccountTariffFilter extends AccountTariff
{
    public $client_account_id = '';
    public $prev_account_tariff_tariff_id = '';
    public $region_id = '';
    public $city_id = '';
    public $is_active = '';

    public $service_type_id = '';
    public $tariff_period_id = '';
    public $beauty_level = '';

    public $infrastructure_project = '';
    public $infrastructure_level = '';
    public $datacenter_id = '';
    public $price_from = '';
    public $price_to = '';

    public $tariff_id = '';
    public $tariff_status_id = '';
    public $tariff_is_include_vat = '';
    public $tariff_is_postpaid = '';
    public $tariff_country_id = '';
    public $tariff_currency_id = '';
    public $tariff_organization_id = '';
    public $tariff_is_default = '';

    public $number_ndc_type_id = '';

    public $tariff_period_utc_from = '';
    public $tariff_period_utc_to = '';

    public $account_log_period_utc_from = '';
    public $account_log_period_utc_to = '';

    /**
     * @param int $serviceTypeId
     */
    public function __construct($serviceTypeId)
    {
        $this->service_type_id = $serviceTypeId;
        parent::__construct();
    }

    /**
     * @return ServiceType
     */
    public function getServiceType()
    {
        return $this->service_type_id ?
            ServiceType::findOne($this->service_type_id) :
            null;
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['beauty_level', 'prev_account_tariff_tariff_id'], 'integer'];
        $rules[] = [['price_from', 'price_to'], 'integer'];
        $rules[] = [
            [
                'tariff_id',
                'tariff_status_id',
                'tariff_is_include_vat',
                'tariff_is_postpaid',
                'tariff_country_id',
                'tariff_organization_id',
                'tariff_is_default'
            ],
            'integer'
        ];
        $rules[] = [['tariff_currency_id'], 'string'];
        $rules[] = [['number_ndc_type_id'], 'integer'];
        $rules[] = [['tariff_period_utc_from', 'tariff_period_utc_to'], 'string'];
        $rules[] = [['account_log_period_utc_from', 'account_log_period_utc_to'], 'string'];
        return $rules;
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = AccountTariff::find()
            ->joinWith('clientAccount')
            ->joinWith('region')
            ->joinWith('tariffPeriod')
            ->leftJoin(Tariff::tableName() . ' tariff', 'tariff.id = ' . TariffPeriod::tableName() . '.tariff_id');

        if (false && $this->serviceType->isPackage()) {
            $query
                ->leftJoin(AccountTariff::tableName() . ' account_tariff_prev', 'account_tariff_prev.id = uu_account_tariff.prev_account_tariff_id')
                ->leftJoin(TariffPeriod::tableName() . ' tariff_period_prev', 'account_tariff_prev.tariff_period_id = tariff_period_prev.id');
        }

        if ($this->service_type_id == ServiceType::ID_VOIP) {
            $query
                ->joinWith('number')
                ->with('number');
            $this->number_ndc_type_id !== '' && $query->andWhere([Number::tableName() . '.ndc_type_id' => $this->number_ndc_type_id]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $accountTariffTableName = AccountTariff::tableName();

        $this->client_account_id !== '' && $query->andWhere([$accountTariffTableName . '.client_account_id' => $this->client_account_id]);
        $this->region_id !== '' && $query->andWhere([$accountTariffTableName . '.region_id' => $this->region_id]);
        $this->city_id !== '' && $query->andWhere([$accountTariffTableName . '.city_id' => $this->city_id]);
        $this->is_active !== '' && $query->andWhere([$accountTariffTableName . '.is_active' => $this->is_active]);

        $this->tariff_status_id !== '' && $query->andWhere(['tariff.tariff_status_id' => $this->tariff_status_id]);
        $this->tariff_is_include_vat !== '' && $query->andWhere(['tariff.is_include_vat' => $this->tariff_is_include_vat]);
        $this->tariff_is_postpaid !== '' && $query->andWhere(['tariff.is_postpaid' => $this->tariff_is_postpaid]);
        $this->tariff_country_id !== '' && $query->andWhere(['tariff.country_id' => $this->tariff_country_id]);
        $this->tariff_currency_id !== '' && $query->andWhere(['tariff.currency_id' => $this->tariff_currency_id]);
        $this->tariff_is_default !== '' && $query->andWhere(['tariff.is_default' => $this->tariff_is_default]);

        if ($this->tariff_organization_id !== '') {
            $query->leftJoin(
                TariffOrganization::tableName() . ' tariff_organization',
                'tariff_organization.tariff_id = tariff.id'
            );
            $query->andWhere(['tariff_organization.organization_id' => $this->tariff_organization_id]);
        }

//        switch ($this->prev_account_tariff_tariff_id) {
//            case '':
//                break;
//            case GetListTrait::$isNull:
//                $query->andWhere(['account_tariff_prev.tariff_period_id' => null]);
//                break;
//            case GetListTrait::$isNotNull:
//                $query->andWhere('account_tariff_prev.tariff_period_id IS NOT NULL');
//                break;
//            default:
//                $query->andWhere(['tariff_period_prev.tariff_id' => $this->prev_account_tariff_tariff_id]);
//                break;
//        }

        switch ($this->tariff_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere(['tariff.id' => null]);
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere('tariff.id IS NOT NULL');
                break;
            default:
                $query->andWhere(['tariff.id' => $this->tariff_id]);
                break;
        }

        $this->voip_number = strtr($this->voip_number, ['.' => '_', '*' => '%']);
        $this->voip_number && $query->andWhere(['LIKE', 'voip_number', $this->voip_number, $isEscape = false]);

        $this->service_type_id && $query->andWhere([$accountTariffTableName . '.service_type_id' => $this->service_type_id]);

        $this->infrastructure_project && $query->andWhere([$accountTariffTableName . '.infrastructure_project' => $this->infrastructure_project]);
        $this->infrastructure_level && $query->andWhere([$accountTariffTableName . '.infrastructure_level' => $this->infrastructure_level]);
        $this->datacenter_id && $query->andWhere([$accountTariffTableName . '.datacenter_id' => $this->datacenter_id]);

        $this->price_from !== '' && $query->andWhere(['>=', $accountTariffTableName . '.price', $this->price_from]);
        $this->price_to !== '' && $query->andWhere(['<=', $accountTariffTableName . '.price', $this->price_to]);

        $this->tariff_period_utc_from !== '' && $query->andWhere([
            '>=',
            $accountTariffTableName . '.tariff_period_utc',
            $this->tariff_period_utc_from
        ]);
        $this->tariff_period_utc_to !== '' && $query->andWhere([
            '<=',
            $accountTariffTableName . '.tariff_period_utc',
            $this->tariff_period_utc_to
        ]);

        $this->account_log_period_utc_from !== '' && $query->andWhere([
            '>=',
            $accountTariffTableName . '.account_log_period_utc',
            $this->account_log_period_utc_from
        ]);
        $this->account_log_period_utc_to !== '' && $query->andWhere([
            '<=',
            $accountTariffTableName . '.account_log_period_utc',
            $this->account_log_period_utc_to
        ]);

        $numberTableName = Number::tableName();
        $this->beauty_level !== '' && $query->andWhere([$numberTableName . '.beauty_level' => $this->beauty_level]);

        switch ($this->tariff_period_id) {
            case '':
                break;
            case TariffPeriod::IS_NOT_SET:
                $query->andWhere([$accountTariffTableName . '.tariff_period_id' => null]);
                break;
            case TariffPeriod::IS_SET:
                $query->andWhere($accountTariffTableName . '.tariff_period_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$accountTariffTableName . '.tariff_period_id' => $this->tariff_period_id]);
                break;
        }

        return $dataProvider;
    }
}
