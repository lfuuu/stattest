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

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['country_code'], 'integer'],
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

        return $dataProvider;
    }
}
