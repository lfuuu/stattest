<?php

namespace app\models\filter;

use app\models\City;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для City
 */
class CityFilter extends City
{
    public $id = '';
    public $name = '';
    public $country_id = '';
    public $connection_point_id = '';
    public $voip_number_format = '';
    public $in_use = '';

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name'], 'string'],
            [['country_id'], 'integer'],
            [['connection_point_id'], 'integer'],
            [['voip_number_format'], 'string'],
            [['in_use'], 'integer'],
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
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->id !== '' && $query->andWhere(['id' => $this->id]);
        $this->name !== '' && $query->andWhere(['LIKE', 'name', $this->name]);
        $this->country_id !== '' && $query->andWhere(['country_id' => $this->country_id]);
        $this->connection_point_id !== '' && $query->andWhere(['connection_point_id' => $this->connection_point_id]);
        $this->voip_number_format !== '' && $query->andWhere(['LIKE', 'voip_number_format', $this->voip_number_format]);
        $this->in_use !== '' && $query->andWhere(['in_use' => $this->in_use]);

        return $dataProvider;
    }
}
