<?php

namespace app\classes\uu\filter;

use app\classes\traits\GetListTrait;
use app\classes\uu\model\AccountLogResource;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\TariffResource;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация AccountLogResource
 */
class AccountLogResourceFilter extends AccountLogResource
{
    public $id = '';

    public $date_from = '';
    public $date_to = '';

    public $amount_use_from = '';
    public $amount_use_to = '';

    public $amount_free_from = '';
    public $amount_free_to = '';

    public $amount_overhead_from = '';
    public $amount_overhead_to = '';

    public $price_per_unit_from = '';
    public $price_per_unit_to = '';

    public $price_from = '';
    public $price_to = '';

    public $client_account_id = '';

    public $account_entry_id = '';

    public $service_type_id = '';
    public $tariff_period_id = '';
    public $tariff_resource_id = ''; // но фактически это resource_id

    /**
     * Вернуть имена полей
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'service_type_id' => 'Тип услуги',
        ] + parent::attributeLabels();
    }

    public function rules()
    {
        return [
            [['id', 'client_account_id', 'tariff_period_id', 'service_type_id', 'tariff_resource_id', 'account_entry_id'], 'integer'],
            [['amount_use_from', 'amount_use_to'], 'double'],
            [['amount_free_from', 'amount_free_to'], 'double'],
            [['amount_overhead_from', 'amount_overhead_to'], 'double'],
            [['price_per_unit_from', 'price_per_unit_to'], 'double'],
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
        $query = AccountLogResource::find()
            ->joinWith('accountTariff')
            ->joinWith('tariffPeriod')
            ->joinWith('tariffResource');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $tariffResourceTableName = TariffResource::tableName();
        $accountLogResourceTableName = AccountLogResource::tableName();
        $accountTariffTableName = AccountTariff::tableName();

        $this->id !== '' && $query->andWhere([$accountLogResourceTableName . '.id' => $this->id]);

        $this->date_from !== '' && $query->andWhere(['>=', $accountLogResourceTableName . '.date', $this->date_from]);
        $this->date_to !== '' && $query->andWhere(['<=', $accountLogResourceTableName . '.date', $this->date_to]);

        $this->amount_use_from !== '' && $query->andWhere(['>=', $accountLogResourceTableName . '.amount_use', $this->amount_use_from]);
        $this->amount_use_to !== '' && $query->andWhere(['<=', $accountLogResourceTableName . '.amount_use', $this->amount_use_to]);

        $this->amount_free_from !== '' && $query->andWhere(['>=', $accountLogResourceTableName . '.amount_free', $this->amount_free_from]);
        $this->amount_free_to !== '' && $query->andWhere(['<=', $accountLogResourceTableName . '.amount_free', $this->amount_free_to]);

        $this->amount_overhead_from !== '' && $query->andWhere(['>=', $accountLogResourceTableName . '.amount_overhead', $this->amount_overhead_from]);
        $this->amount_overhead_to !== '' && $query->andWhere(['<=', $accountLogResourceTableName . '.amount_overhead', $this->amount_overhead_to]);

        $this->price_per_unit_from !== '' && $query->andWhere(['>=', $accountLogResourceTableName . '.price_per_unit', $this->price_per_unit_from]);
        $this->price_per_unit_to !== '' && $query->andWhere(['<=', $accountLogResourceTableName . '.price_per_unit', $this->price_per_unit_to]);

        $this->price_from !== '' && $query->andWhere(['>=', $accountLogResourceTableName . '.price', $this->price_from]);
        $this->price_to !== '' && $query->andWhere(['<=', $accountLogResourceTableName . '.price', $this->price_to]);

        $this->client_account_id !== '' && $query->andWhere([$accountTariffTableName . '.client_account_id' => $this->client_account_id]);

        switch ($this->account_entry_id) {
            case GetListTrait::$isNull:
                $query->andWhere([$accountLogResourceTableName . '.account_entry_id' => null]);
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($accountLogResourceTableName . '.account_entry_id IS NOT NULL');
                break;
            default:
                break;
        }

        if (!$this->service_type_id) {
            $this->tariff_period_id = '';
            $this->tariff_resource_id = '';
        }
        $this->service_type_id !== '' && $query->andWhere([$accountTariffTableName . '.service_type_id' => $this->service_type_id]);
        $this->tariff_period_id !== '' && $query->andWhere([$accountLogResourceTableName . '.tariff_period_id' => $this->tariff_period_id]);
        $this->tariff_resource_id !== '' && $query->andWhere([$tariffResourceTableName . '.resource_id' => $this->tariff_resource_id]);

        return $dataProvider;
    }
}
