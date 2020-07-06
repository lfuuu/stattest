<?php

namespace app\modules\nnp2\filters;

use app\modules\nnp2\models\City;
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
    public $parent_id = '';
    public $is_valid = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'name_translit'], 'string'],
            [['id', 'country_code', 'region_id', 'cnt_from', 'cnt_to', 'parent_id', 'is_valid'], 'integer'],
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

        $this->parent_id && $query->andWhere([$cityTableName . '.parent_id' => $this->parent_id]);
        if (
            ($this->is_valid !== '')
            && !is_null($this->is_valid)
        ) {
            $query->andWhere([$cityTableName . '.is_valid' => (bool)$this->is_valid]);
        }

        $sort = \Yii::$app->request->get('sort');
        if (!$sort) {
            $query->addOrderBy(['id' => SORT_ASC]);
        }

        return $dataProvider;
    }
}
