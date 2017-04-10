<?php

namespace app\modules\uu\filter;

use app\classes\traits\GetListTrait;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountTariff;
use yii\data\ActiveDataProvider;

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

    public function rules()
    {
        return [
            [['id', 'client_account_id', 'tariff_period_id', 'service_type_id', 'account_entry_id'], 'integer'],

            [['period_price_from', 'coefficient_from', 'price_from'], 'double'],
            [['period_price_to', 'coefficient_to', 'price_to'], 'double'],

            [['date_from_from', 'date_to_from'], 'string', 'max' => 255],
            [['date_from_to', 'date_to_to'], 'string', 'max' => 255],
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

        if (!$this->service_type_id) {
            $this->tariff_period_id = '';
        }
        $this->service_type_id !== '' && $query->andWhere([$accountTariffTableName . '.service_type_id' => $this->service_type_id]);
        $this->tariff_period_id !== '' && $query->andWhere([$accountLogPeriodTableName . '.tariff_period_id' => $this->tariff_period_id]);

        return $dataProvider;
    }
}
