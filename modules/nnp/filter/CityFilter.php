<?php

namespace app\modules\nnp\filter;

use app\modules\nnp\models\City;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для City
 */
class CityFilter extends City
{
    public $name = '';
    public $country_prefix = '';

    public function rules()
    {
        return [
            [['name'], 'string'],
            [['country_prefix'], 'integer'],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = City::find();
        $cityTableName = City::tableName();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->name && $query->andWhere(['LIKE', $cityTableName . '.name', $this->name]);
        $this->country_prefix && $query->andWhere([$cityTableName . '.country_prefix' => $this->country_prefix]);

        return $dataProvider;
    }
}
