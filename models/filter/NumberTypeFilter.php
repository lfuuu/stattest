<?php

namespace app\models\filter;

use app\models\NumberType;
use app\models\NumberTypeCountry;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для NumberType
 */
class NumberTypeFilter extends NumberType
{
    public $name = '';
    public $number_type_country_id = '';

    public function rules()
    {
        return [
            [['name'], 'string'],
            [['number_type_country_id'], 'integer'],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = NumberType::find()
            ->with('numberTypeCountries');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $numberTypeTableName = NumberType::tableName();
        $numberTypeCountryTableName = NumberTypeCountry::tableName();

        $this->name !== '' && $query->andWhere(['LIKE', $numberTypeTableName . '.name', $this->name]);
        $this->number_type_country_id !== '' && $query->joinWith('numberTypeCountries')->andWhere([$numberTypeCountryTableName . '.country_id' => $this->number_type_country_id]);

        return $dataProvider;
    }
}
