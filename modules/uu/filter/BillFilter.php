<?php

namespace app\modules\uu\filter;

use app\modules\uu\models\Bill;
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
    public $is_converted = '';

    public function rules()
    {
        return [
            [['id', 'client_account_id', 'is_converted'], 'integer'],
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
            'sort' => [
                'defaultOrder' => [
                    'date' => SORT_DESC,
                ]
            ],
        ]);

        $this->id !== '' && $query->andWhere(['id' => $this->id]);

        $this->date !== '' && $query->andWhere('DATE_FORMAT(date, "%Y-%m") = :date', [':date' => $this->date]);

        $this->price_from !== '' && $query->andWhere(['>=', 'price', $this->price_from]);
        $this->price_to !== '' && $query->andWhere(['<=', 'price', $this->price_to]);
        $this->is_converted !== '' && $query->andWhere(['is_converted' => $this->is_converted]);

        $this->client_account_id !== '' && $query->andWhere(['client_account_id' => $this->client_account_id]);

        return $dataProvider;
    }
}
