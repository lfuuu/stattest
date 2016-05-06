<?php

namespace app\classes\uu\filter;

use app\classes\uu\model\AccountLogSetup;
use app\classes\uu\model\AccountTariff;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация AccountLogSetup
 */
class AccountLogSetupFilter extends AccountLogSetup
{
    public $id = '';

    public $date_from = '';
    public $date_to = '';

    public $price_from = '';
    public $price_to = '';

    public $client_account_id = '';

    public $service_type_id = '';
    public $tariff_period_id = '';

    public function rules()
    {
        return [
            [['id', 'client_account_id', 'tariff_period_id', 'service_type_id'], 'integer'],
            [['price_from', 'price_to'], 'double'],
            [['date_from', 'date_to'], 'string', 'max' => 255],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = AccountLogSetup::find()
            ->joinWith('accountTariff')
            ->joinWith('tariffPeriod');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $accountLogSetupTableName = AccountLogSetup::tableName();
        $accountTariffTableName = AccountTariff::tableName();

        $this->id !== '' && $query->andWhere([$accountLogSetupTableName . '.id' => $this->id]);

        $this->date_from !== '' && $query->andWhere($accountLogSetupTableName . '.date >= :date_from',
            [':date_from' => $this->date_from]);
        $this->date_to !== '' && $query->andWhere($accountLogSetupTableName . '.date <= :date_to',
            [':date_to' => $this->date_to]);

        $this->price_from !== '' && $query->andWhere($accountLogSetupTableName . '.price >= :price_from',
            [':price_from' => $this->price_from]);
        $this->price_to !== '' && $query->andWhere($accountLogSetupTableName . '.price <= :price_to',
            [':price_to' => $this->price_to]);

        $this->client_account_id !== '' && $query->andWhere([$accountTariffTableName . '.client_account_id' => $this->client_account_id]);

        if (!$this->service_type_id) {
            $this->tariff_period_id = '';
        }
        $this->service_type_id !== '' && $query->andWhere([$accountTariffTableName . '.service_type_id' => $this->service_type_id]);
        $this->tariff_period_id !== '' && $query->andWhere([$accountLogSetupTableName . '.tariff_period_id' => $this->tariff_period_id]);

        return $dataProvider;
    }
}
