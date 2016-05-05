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

    public $price_without_vat_from = '';
    public $price_without_vat_to = '';

    public $price_with_vat_from = '';
    public $price_with_vat_to = '';

    public $vat_from = '';
    public $vat_to = '';

    public $vat_rate_from = '';
    public $vat_rate_to = '';

    public $account_tariff_id = '';
    public $service_type_id = '';
    public $client_account_id = '';

    public $type_id = '';

    public function rules()
    {
        return [
            [['id', 'client_account_id', 'account_tariff_id', 'service_type_id', 'type_id'], 'integer'],
            [['price_from', 'price_to'], 'double'],
            [['price_without_vat_from', 'price_without_vat_to'], 'double'],
            [['price_with_vat_from', 'price_with_vat_to'], 'double'],
            [['vat_from', 'vat_to'], 'double'],
            [['vat_rate_from', 'vat_rate_to'], 'integer'],
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

        $this->price_from !== '' && $query->andWhere(['>=', $accountEntryTableName . '.price', $this->price_from]);
        $this->price_to !== '' && $query->andWhere(['<=', $accountEntryTableName . '.price', $this->price_to]);

        $this->price_without_vat_from !== '' && $query->andWhere(['>=', $accountEntryTableName . '.price_without_vat', $this->price_without_vat_from]);
        $this->price_without_vat_to !== '' && $query->andWhere(['<=', $accountEntryTableName . '.price_without_vat', $this->price_without_vat_to]);

        $this->price_with_vat_from !== '' && $query->andWhere(['>=', $accountEntryTableName . '.price_with_vat', $this->price_with_vat_from]);
        $this->price_with_vat_to !== '' && $query->andWhere(['<=', $accountEntryTableName . '.price_with_vat', $this->price_with_vat_to]);

        $this->vat_from !== '' && $query->andWhere(['>=', $accountEntryTableName . '.vat', $this->vat_from]);
        $this->vat_to !== '' && $query->andWhere(['<=', $accountEntryTableName . '.vat', $this->vat_to]);

        $this->vat_rate_from !== '' && $query->andWhere(['>=', $accountEntryTableName . '.vat_rate', $this->vat_rate_from]);
        $this->vat_rate_to !== '' && $query->andWhere(['<=', $accountEntryTableName . '.vat_rate', $this->vat_rate_to]);

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
