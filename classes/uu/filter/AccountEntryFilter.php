<?php

namespace app\classes\uu\filter;

use app\classes\uu\model\AccountEntry;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\TariffResource;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация AccountEntry
 */
class AccountEntryFilter extends AccountEntry
{
    public $id = '';

    public $date = '';

    public $price_from = '';
    public $price_to = '';

    public $account_tariff_id = '';
    public $service_type_id = '';
    public $client_account_id = '';

    public $type_id = '';

    public function rules()
    {
        return [
            [['id', 'client_account_id', 'account_tariff_id', 'service_type_id', 'type_id'], 'integer'],
            [['price_from', 'price_to'], 'double'],
            [['date'], 'string', 'max' => 255],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = AccountEntry::find()
            ->joinWith('accountTariff');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $accountEntryTableName = AccountEntry::tableName();
        $accountTariffTableName = AccountTariff::tableName();

        $this->id !== '' && $query->andWhere([$accountEntryTableName . '.id' => $this->id]);

        $this->date !== '' && $query->andWhere([$accountEntryTableName . '.date' => $this->date . '-01']);

        $this->price_from !== '' && $query->andWhere($accountEntryTableName . '.price >= :price_from', [':price_from' => $this->price_from]);
        $this->price_to !== '' && $query->andWhere($accountEntryTableName . '.price <= :price_to', [':price_to' => $this->price_to]);

        $this->account_tariff_id !== '' && $query->andWhere([$accountEntryTableName . '.account_tariff_id' => $this->account_tariff_id]);
        $this->service_type_id !== '' && $query->andWhere([$accountTariffTableName . '.service_type_id' => $this->service_type_id]);
        $this->client_account_id !== '' && $query->andWhere([$accountTariffTableName . '.client_account_id' => $this->client_account_id]);

        if ($this->type_id !== '') {
            if ($this->type_id < 0) {
                // подключение или абонентка
                $query->andWhere([$accountEntryTableName . '.type_id' => $this->type_id]);
            } else {
                // ресурс
                $query->joinWith('tariffResource');
                $tariffResourceTableName = TariffResource::tableName();
                $query->andWhere([$tariffResourceTableName . '.resource_id' => $this->type_id]);
            }
        }

        return $dataProvider;
    }
}
