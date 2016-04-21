<?php

namespace app\classes\uu\filter;

use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\TariffPeriod;
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
        return ServiceType::findOne($this->service_type_id);
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

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $accountTariffTableName = AccountTariff::tableName();

        $this->client_account_id !== '' && $query->andWhere([$accountTariffTableName . '.client_account_id' => $this->client_account_id]);
        $this->region_id !== '' && $query->andWhere([$accountTariffTableName . '.region_id' => $this->region_id]);
        $this->city_id !== '' && $query->andWhere([$accountTariffTableName . '.city_id' => $this->city_id]);
        $this->is_active !== '' && $query->andWhere([$accountTariffTableName . '.is_active' => $this->is_active]);

        // если ['LIKE', 'number', $mask], то он заэскейпит спецсимволы и добавить % в начало и конец. Подробнее см. \yii\db\QueryBuilder::buildLikeCondition
        if ($this->voip_number !== '' &&
            ($this->voip_number = strtr($this->voip_number, ['.' => '_', '*' => '%'])) &&
            preg_match('/^[\d_%]+$/', $this->voip_number)
        ) {
            $query->andWhere('voip_number LIKE :voip_number', [':voip_number' => $this->voip_number]);
        } else {
            $this->voip_number = '';
        }

        $this->service_type_id !== '' && $query->andWhere([$accountTariffTableName . '.service_type_id' => $this->service_type_id]);
        if ($this->service_type_id !== '' && $this->tariff_period_id !== '') {
            switch ($this->tariff_period_id) {
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
        }

        return $dataProvider;
    }
}
