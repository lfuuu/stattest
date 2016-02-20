<?php

namespace app\models\filter;

use app\models\CurrencyRate;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для CurrencyRate
 */
class CurrencyRateFilter extends CurrencyRate
{
    public $date_from = '';
    public $date_to = '';

    public $currency = '';

    public $rate_from = '';
    public $rate_to = '';

    public function rules()
    {
        return [
            [['date_from', 'date_to', 'currency'], 'string'],
            [['rate_from', 'rate_to'], 'integer'],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = CurrencyRate::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'date' => SORT_DESC
                ]
            ]
        ]);

        $this->date_from !== '' && $query->andWhere(['>=', 'date', $this->date_from]);
        $this->date_to !== '' && $query->andWhere(['<=', 'date', $this->date_to]);

        $this->currency !== '' && $query->andWhere(['currency' => $this->currency]);

        $this->rate_from !== '' && $query->andWhere(['>=', 'rate', $this->rate_from]);
        $this->rate_to !== '' && $query->andWhere(['<=', 'rate', $this->rate_to]);

        return $dataProvider;
    }
}
