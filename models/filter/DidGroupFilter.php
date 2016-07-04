<?php

namespace app\models\filter;

use app\models\City;
use app\models\DidGroup;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для DidGroup
 */
class DidGroupFilter extends DidGroup
{
    public $country_id = '';
    public $city_id = '';
    public $name = '';
    public $beauty_level = '';
    public $number_type_id = '';

    public function rules()
    {
        return [
            [['country_id'], 'integer'],
            [['city_id'], 'integer'],
            [['name'], 'string'],
            [['beauty_level'], 'integer'],
            [['number_type_id'], 'integer'],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = DidGroup::find()
            ->joinWith('city');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $didGroupTableName = DidGroup::tableName();
        $cityTableName = City::tableName();

        $this->country_id !== '' && $query->andWhere([$cityTableName . '.country_id' => $this->country_id]);
        $this->city_id !== '' && $query->andWhere([$didGroupTableName . '.city_id' => $this->city_id]);
        $this->name !== '' && $query->andWhere(['LIKE', $didGroupTableName . '.name', $this->name]);
        $this->beauty_level !== '' && $query->andWhere([$didGroupTableName . '.beauty_level' => $this->beauty_level]);
        $this->number_type_id !== '' && $query->andWhere([$didGroupTableName . '.number_type_id' => $this->number_type_id]);

        return $dataProvider;
    }
}
