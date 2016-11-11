<?php

namespace app\models\filter;

use app\models\DidGroup;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для DidGroup
 */
class DidGroupFilter extends DidGroup
{
    public $id = '';
    public $country_code = '';
    public $city_id = '';
    public $name = '';
    public $beauty_level = '';
    public $number_type_id = '';

    public $price1_from = '';
    public $price1_to = '';

    public $price2_from = '';
    public $price2_to = '';

    public $price3_from = '';
    public $price3_to = '';

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['country_code'], 'integer'],
            [['city_id'], 'integer'],
            [['name'], 'string'],
            [['beauty_level'], 'integer'],
            [['number_type_id'], 'integer'],
            [['price1_from', 'price1_to'], 'number'],
            [['price2_from', 'price2_to'], 'number'],
            [['price3_from', 'price3_to'], 'number'],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = DidGroup::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $didGroupTableName = DidGroup::tableName();

        $this->id !== '' && $query->andWhere([$didGroupTableName . '.id' => $this->id]);
        $this->country_code !== '' && $query->andWhere([$didGroupTableName . '.country_code' => $this->country_code]);
        $this->city_id !== '' && $query->andWhere([$didGroupTableName . '.city_id' => $this->city_id]);
        $this->name !== '' && $query->andWhere(['LIKE', $didGroupTableName . '.name', $this->name]);
        $this->beauty_level !== '' && $query->andWhere([$didGroupTableName . '.beauty_level' => $this->beauty_level]);
        $this->number_type_id !== '' && $query->andWhere([$didGroupTableName . '.number_type_id' => $this->number_type_id]);

        $this->price1_from !== '' && $query->andWhere(['>=', $didGroupTableName . '.price1', $this->price1_from]);
        $this->price1_to !== '' && $query->andWhere(['<=', $didGroupTableName . '.price1', $this->price1_to]);

        $this->price2_from !== '' && $query->andWhere(['>=', $didGroupTableName . '.price2', $this->price2_from]);
        $this->price2_to !== '' && $query->andWhere(['<=', $didGroupTableName . '.price2', $this->price2_to]);

        $this->price3_from !== '' && $query->andWhere(['>=', $didGroupTableName . '.price3', $this->price3_from]);
        $this->price3_to !== '' && $query->andWhere(['<=', $didGroupTableName . '.price3', $this->price3_to]);

        return $dataProvider;
    }
}
