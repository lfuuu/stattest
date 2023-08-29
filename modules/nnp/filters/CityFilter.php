<?php

namespace app\modules\nnp\filters;

use app\modules\nnp\models\City;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для City
 */
class CityFilter extends City
{
    public $id = '';
    public $name = '';
    public $name_translit = '';
    public $country_code = '';
    public $region_id = '';
    public $cnt_from = '';
    public $cnt_to = '';
    public $cnt_active_from = '';
    public $cnt_active_to = '';
    public $parent_id = '';
    public $is_valid = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'name_translit', 'parent_id'], 'string'],
            [['id', 'country_code', 'region_id', 'cnt_from', 'cnt_to', 'cnt_active_from', 'cnt_active_to', 'is_valid'], 'integer'],
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
        $this->name_translit && $query->andWhere(['LIKE', $cityTableName . '.name_translit', $this->name_translit]);
        $this->id && $query->andWhere([$cityTableName . '.id' => $this->id]);
        $this->country_code && $query->andWhere([$cityTableName . '.country_code' => $this->country_code]);
        $this->region_id && $query->andWhere([$cityTableName . '.region_id' => $this->region_id]);

        $this->cnt_from !== '' && $query->andWhere(['>=', $cityTableName . '.cnt', $this->cnt_from]);
        $this->cnt_to !== '' && $query->andWhere(['<=', $cityTableName . '.cnt', $this->cnt_to]);

        $this->cnt_active_from !== '' && $query->andWhere(['>=', $cityTableName . '.cnt_active', $this->cnt_active_from]);
        $this->cnt_active_to !== '' && $query->andWhere(['<=', $cityTableName . '.cnt_active', $this->cnt_active_to]);

        $this->is_valid !== '' && $query->andWhere(["{$cityTableName}.is_valid" => (bool)$this->is_valid]);

        if ($this->parent_id !== '') {
            $query->joinWith('parent p')->andWhere(['LIKE', 'p.name', $this->parent_id, true]);
        }

        $query->with('parent');

        return $dataProvider;
    }
}
