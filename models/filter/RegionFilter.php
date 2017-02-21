<?php

namespace app\models\filter;

use app\models\Region;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для Region
 */
class RegionFilter extends Region
{
    public $id = '';
    public $name = '';
    public $short_name = '';
    public $code = '';
    public $timezone_name = '';
    public $country_id = '';
    public $is_active = '';

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = Region::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->id !== '' && $query->andWhere(['id' => $this->id]);
        $this->name !== '' && $query->andWhere(['LIKE', 'name', $this->name]);
        $this->short_name !== '' && $query->andWhere(['LIKE', 'short_name', $this->short_name]);
        $this->timezone_name !== '' && $query->andWhere(['LIKE', 'timezone_name', $this->timezone_name]);
        $this->code !== '' && $query->andWhere(['code' => $this->code]);
        $this->country_id !== '' && $query->andWhere(['country_id' => $this->country_id]);
        $this->is_active !== '' && $query->andWhere(['is_active' => $this->is_active]);

        return $dataProvider;
    }
}
