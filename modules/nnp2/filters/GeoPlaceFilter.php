<?php

namespace app\modules\nnp2\filters;

use app\modules\nnp2\models\GeoPlace;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для GeoPlace
 */
class GeoPlaceFilter extends GeoPlace
{
    public $id = '';
    public $ndc = '';
    public $country_code = '';
    public $region_id = '';
    public $city_id = '';
    public $parent_id = '';
    public $is_valid = '';

    public $sort;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'country_code', 'region_id', 'city_id', 'parent_id', 'is_valid'], 'integer'],
            [['ndc'], 'string'],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = GeoPlace::find();
        $geoTableName = GeoPlace::tableName();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->id && $query->andWhere([$geoTableName . '.id' => $this->id]);
        $this->ndc && $query->andWhere(['LIKE', $geoTableName . '.ndc', $this->ndc]);
        $this->country_code && $query->andWhere([$geoTableName . '.country_code' => $this->country_code]);
        $this->region_id && $query->andWhere([$geoTableName . '.region_id' => $this->region_id]);

        $this->parent_id && $query->andWhere([$geoTableName . '.parent_id' => $this->parent_id]);
        if (
            ($this->is_valid !== '')
            && !is_null($this->is_valid)
        ) {
            $query->andWhere([$geoTableName . '.is_valid' => (bool)$this->is_valid]);
        }

        if (!$this->sort) {
            $query->addOrderBy(['id' => SORT_ASC]);
        }

        return $dataProvider;
    }
}
