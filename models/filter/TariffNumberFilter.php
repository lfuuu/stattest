<?php

namespace app\models\filter;

use app\models\TariffNumber;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для TariffNumber
 */
class TariffNumberFilter extends TariffNumber
{
    public $id = '';
    public $country_id = '';
    public $city_id = '';
    public $name = '';
    public $currency_id = '';

    public $activation_fee_from = '';
    public $activation_fee_to = '';

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name', 'currency_id'], 'string'],
            [['country_id', 'city_id'], 'integer'],
            [['activation_fee_from', 'activation_fee_to'], 'integer'],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = TariffNumber::find()
            ->joinWith(['country', 'city']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $tariffNumberTableName = TariffNumber::tableName();

        $this->id !== '' && $query->andWhere([$tariffNumberTableName . '.id' => $this->id]);
        $this->name !== '' && $query->andWhere(['LIKE', $tariffNumberTableName . '.name', $this->name]);
        $this->country_id !== '' && $query->andWhere([$tariffNumberTableName . '.country_id' => $this->country_id]);
        $this->city_id !== '' && $query->andWhere([$tariffNumberTableName . '.city_id' => $this->city_id]);
        $this->currency_id !== '' && $query->andWhere([$tariffNumberTableName . '.currency_id' => $this->currency_id]);

        $this->activation_fee_from !== '' && $query->andWhere(['>=', $tariffNumberTableName . '.activation_fee', $this->activation_fee_from]);
        $this->activation_fee_to !== '' && $query->andWhere(['<=', $tariffNumberTableName . '.activation_fee', $this->activation_fee_to]);

        return $dataProvider;
    }
}
