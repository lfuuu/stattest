<?php

namespace app\modules\uu\filter;

use app\classes\traits\GetListTrait;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\TariffPeriod;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

/**
 * Фильтрация AccountLogPeriod
 */
class AccountLogPeriodFilter extends AccountLogPeriod
{
    public $id = '';

    public $date_from_from = '';
    public $date_from_to = '';

    public $date_to_from = '';
    public $date_to_to = '';

    public $period_price_from = '';
    public $period_price_to = '';

    public $coefficient_from = '';
    public $coefficient_to = '';

    public $price_from = '';
    public $price_to = '';

    public $client_account_id = '';

    public $account_entry_id = '';

    public $service_type_id = '';
    public $tariff_period_id = '';

    public $account_tariff_infrastructure_project = '';
    public $account_tariff_infrastructure_level = '';
    public $account_tariff_datacenter_id = '';
    public $account_tariff_city_id = '';
    public $account_tariff_price_from = '';
    public $account_tariff_price_to = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'client_account_id', 'tariff_period_id', 'service_type_id', 'account_entry_id'], 'integer'],

            [['period_price_from', 'coefficient_from', 'price_from'], 'double'],
            [['period_price_to', 'coefficient_to', 'price_to'], 'double'],

            [['date_from_from', 'date_to_from'], 'string', 'max' => 10],
            [['date_from_to', 'date_to_to'], 'string', 'max' => 10],

            [
                [
                    'account_tariff_infrastructure_project',
                    'account_tariff_infrastructure_level',
                    'account_tariff_datacenter_id',
                    'account_tariff_city_id',
                    'account_tariff_price_from',
                    'account_tariff_price_to',
                ],
                'integer'
            ],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = AccountLogPeriod::find()
            ->joinWith('accountTariff')
            ->joinWith('tariffPeriod');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $accountLogPeriodTableName = AccountLogPeriod::tableName();
        $accountTariffTableName = AccountTariff::tableName();

        $this->id !== '' && $query->andWhere([$accountLogPeriodTableName . '.id' => $this->id]);

        $this->date_from_from !== '' && $query->andWhere(['>=', $accountLogPeriodTableName . '.date_from', $this->date_from_from]);
        $this->date_from_to !== '' && $query->andWhere(['<=', $accountLogPeriodTableName . '.date_from', $this->date_from_to]);

        $this->date_to_from !== '' && $query->andWhere(['>=', $accountLogPeriodTableName . '.date_to', $this->date_to_from]);
        $this->date_to_to !== '' && $query->andWhere(['<=', $accountLogPeriodTableName . '.date_to', $this->date_to_to]);

        $this->period_price_from !== '' && $query->andWhere(['>=', $accountLogPeriodTableName . '.period_price', $this->period_price_from]);
        $this->period_price_to !== '' && $query->andWhere(['<=', $accountLogPeriodTableName . '.period_price', $this->period_price_to]);

        $this->coefficient_from !== '' && $query->andWhere(['>=', $accountLogPeriodTableName . '.coefficient', $this->coefficient_from]);
        $this->coefficient_to !== '' && $query->andWhere(['<=', $accountLogPeriodTableName . '.coefficient', $this->coefficient_to]);

        $this->price_from !== '' && $query->andWhere(['>=', $accountLogPeriodTableName . '.price', $this->price_from]);
        $this->price_to !== '' && $query->andWhere(['<=', $accountLogPeriodTableName . '.price', $this->price_to]);

        $this->client_account_id !== '' && $query->andWhere([$accountTariffTableName . '.client_account_id' => $this->client_account_id]);
        $this->account_tariff_infrastructure_project !== '' && $query->andWhere([$accountTariffTableName . '.infrastructure_project' => $this->account_tariff_infrastructure_project]);
        $this->account_tariff_infrastructure_level !== '' && $query->andWhere([$accountTariffTableName . '.infrastructure_level' => $this->account_tariff_infrastructure_level]);
        $this->account_tariff_datacenter_id !== '' && $query->andWhere([$accountTariffTableName . '.datacenter_id' => $this->account_tariff_datacenter_id]);
        $this->account_tariff_city_id !== '' && $query->andWhere([$accountTariffTableName . '.city_id' => $this->account_tariff_city_id]);
        $this->account_tariff_price_from !== '' && $query->andWhere(['>=', $accountTariffTableName . '.price', $this->account_tariff_price_from]);
        $this->account_tariff_price_to !== '' && $query->andWhere(['<=', $accountTariffTableName . '.price', $this->account_tariff_price_to]);

        switch ($this->account_entry_id) {
            case GetListTrait::$isNull:
                $query->andWhere([$accountLogPeriodTableName . '.account_entry_id' => null]);
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($accountLogPeriodTableName . '.account_entry_id IS NOT NULL');
                break;
            default:
                break;
        }

        $this->service_type_id !== '' && $query->andWhere([$accountTariffTableName . '.service_type_id' => $this->service_type_id]);

        switch ($this->tariff_period_id) {
            case '':
                break;
            case TariffPeriod::IS_NOT_SET:
                $query->andWhere([$accountLogPeriodTableName . '.tariff_period_id' => null]);
                break;
            case TariffPeriod::IS_SET:
                $query->andWhere($accountLogPeriodTableName . '.tariff_period_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$accountLogPeriodTableName . '.tariff_period_id' => $this->tariff_period_id]);
                break;
        }

        return $dataProvider;
    }

    /**
     * Итого
     *
     * @return array
     */
    public function searchSummary()
    {
        $dataProvider = $this->search();
        $accountLogPeriodTableName = AccountLogPeriod::tableName();
        $accountTariffTableName = AccountTariff::tableName();

        /** @var ActiveQuery $query */
        $query = $dataProvider->query;
        return $query->select([
            'account_log_period_price' => 'SUM(' . $accountLogPeriodTableName . '.price)',
            'account_tariff_price' => 'SUM(' . $accountTariffTableName . '.price)',
        ])
        ->asArray()
        ->one();
    }
}
