<?php

namespace app\modules\uu\filter;

use app\classes\traits\GetListTrait;
use app\modules\uu\models\AccountLogMin;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\TariffPeriod;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация AccountLogMin
 */
class AccountLogMinFilter extends AccountLogMin
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
        $query = AccountLogMin::find()
            ->joinWith('accountTariff')
            ->joinWith('tariffPeriod');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $accountLogMinTableName = AccountLogMin::tableName();
        $accountTariffTableName = AccountTariff::tableName();

        $this->id !== '' && $query->andWhere([$accountLogMinTableName . '.id' => $this->id]);

        $this->date_from_from !== '' && $query->andWhere(['>=', $accountLogMinTableName . '.date_from', $this->date_from_from]);
        $this->date_from_to !== '' && $query->andWhere(['<=', $accountLogMinTableName . '.date_from', $this->date_from_to]);

        $this->date_to_from !== '' && $query->andWhere(['>=', $accountLogMinTableName . '.date_to', $this->date_to_from]);
        $this->date_to_to !== '' && $query->andWhere(['<=', $accountLogMinTableName . '.date_to', $this->date_to_to]);

        $this->period_price_from !== '' && $query->andWhere(['>=', $accountLogMinTableName . '.period_price', $this->period_price_from]);
        $this->period_price_to !== '' && $query->andWhere(['<=', $accountLogMinTableName . '.period_price', $this->period_price_to]);

        $this->coefficient_from !== '' && $query->andWhere(['>=', $accountLogMinTableName . '.coefficient', $this->coefficient_from]);
        $this->coefficient_to !== '' && $query->andWhere(['<=', $accountLogMinTableName . '.coefficient', $this->coefficient_to]);

        $this->price_from !== '' && $query->andWhere(['>=', $accountLogMinTableName . '.price', $this->price_from]);
        $this->price_to !== '' && $query->andWhere(['<=', $accountLogMinTableName . '.price', $this->price_to]);

        $this->client_account_id !== '' && $query->andWhere([$accountTariffTableName . '.client_account_id' => $this->client_account_id]);

        switch ($this->account_entry_id) {
            case GetListTrait::$isNull:
                $query->andWhere([$accountLogMinTableName . '.account_entry_id' => null]);
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($accountLogMinTableName . '.account_entry_id IS NOT NULL');
                break;
            default:
                break;
        }

        $this->service_type_id !== '' && $query->andWhere([$accountTariffTableName . '.service_type_id' => $this->service_type_id]);

        switch ($this->tariff_period_id) {
            case '':
                break;
            case TariffPeriod::IS_NOT_SET:
                $query->andWhere([$accountLogMinTableName . '.tariff_period_id' => null]);
                break;
            case TariffPeriod::IS_SET:
                $query->andWhere($accountLogMinTableName . '.tariff_period_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$accountLogMinTableName . '.tariff_period_id' => $this->tariff_period_id]);
                break;
        }

        return $dataProvider;
    }
}
