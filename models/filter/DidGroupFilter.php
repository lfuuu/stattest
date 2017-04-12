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

    public $price4_from = '';
    public $price4_to = '';

    public $price5_from = '';
    public $price5_to = '';

    public $price6_from = '';
    public $price6_to = '';

    public $price7_from = '';
    public $price7_to = '';

    public $price8_from = '';
    public $price8_to = '';

    public $price9_from = '';
    public $price9_to = '';


    /**
     * Правила
     */
    public function rules()
    {
        $rules = [
            [['id'], 'integer'],
            [['country_code'], 'integer'],
            [['city_id'], 'integer'],
            [['name'], 'string'],
            [['beauty_level'], 'integer'],
            [['number_type_id'], 'integer'],
        ];

        for ($i = 1; $i <= 9; $i++) {
            $rules[] = [['price' . $i . '_from', 'price' . $i . '_to'], 'number'];
        }
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

        for ($i = 1; $i <= 9; $i++) {
            $this->{'price' . $i .'_from'} !== '' && $query->andWhere(['>=', $didGroupTableName . '.price' . $i, $this->{'price' . $i . '_from'}]);
            $this->{'price' . $i .'_to'} !== '' && $query->andWhere(['<=', $didGroupTableName . '.price' . $i, $this->{'price' . $i . '_to'}]);
        }

        return $dataProvider;
    }
}
