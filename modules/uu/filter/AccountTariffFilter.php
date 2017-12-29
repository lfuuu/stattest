<?php

namespace app\modules\uu\filter;

use app\models\Number;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\TariffPeriod;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для AccountTariff
 */
class AccountTariffFilter extends AccountTariff
{
    public $client_account_id = '';
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
        $rules[] = [['beauty_level'], 'integer'];
        $rules[] = [['price_from', 'price_to'], 'integer'];
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
            ->joinWith('tariffPeriod');

        if ($this->service_type_id == ServiceType::ID_VOIP) {
            $query
                ->joinWith('number')
                ->with('number');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $accountTariffTableName = AccountTariff::tableName();

        $this->client_account_id !== '' && $query->andWhere([$accountTariffTableName . '.client_account_id' => $this->client_account_id]);
        $this->region_id !== '' && $query->andWhere([$accountTariffTableName . '.region_id' => $this->region_id]);
        $this->city_id !== '' && $query->andWhere([$accountTariffTableName . '.city_id' => $this->city_id]);
        $this->is_active !== '' && $query->andWhere([$accountTariffTableName . '.is_active' => $this->is_active]);

        $this->voip_number = strtr($this->voip_number, ['.' => '_', '*' => '%']);
        $this->voip_number && $query->andWhere(['LIKE', 'voip_number', $this->voip_number, $isEscape = false]);

        $this->service_type_id && $query->andWhere([$accountTariffTableName . '.service_type_id' => $this->service_type_id]);

        $this->infrastructure_project && $query->andWhere([$accountTariffTableName . '.infrastructure_project' => $this->infrastructure_project]);
        $this->infrastructure_level && $query->andWhere([$accountTariffTableName . '.infrastructure_level' => $this->infrastructure_level]);
        $this->datacenter_id && $query->andWhere([$accountTariffTableName . '.datacenter_id' => $this->datacenter_id]);
        $this->price_from !== '' && $query->andWhere(['>=', $accountTariffTableName . '.price', $this->price_from]);
        $this->price_to !== '' && $query->andWhere(['<=', $accountTariffTableName . '.price', $this->price_to]);

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
