<?php

namespace app\classes\uu\filter;

use app\classes\uu\model\Bill;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация Bill
 */
class BillFilter extends Bill
{
    public $id = '';

    public $date = '';

    public $price_from = '';
    public $price_to = '';

    public $client_account_id = '';

    public function rules()
    {
        return [
            [['id', 'client_account_id'], 'integer'],
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
        $query = Bill::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->id !== '' && $query->andWhere(['id' => $this->id]);

        $this->date !== '' && $query->andWhere(['date' => $this->date . '-01']);

        $this->price_from !== '' && $query->andWhere('price >= :price_from', [':price_from' => $this->price_from]);
        $this->price_to !== '' && $query->andWhere('price <= :price_to', [':price_to' => $this->price_to]);

        $this->client_account_id !== '' && $query->andWhere(['client_account_id' => $this->client_account_id]);

        return $dataProvider;
    }
}
