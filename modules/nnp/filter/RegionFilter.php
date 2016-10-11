<?php

namespace app\modules\nnp\filter;

use app\modules\nnp\models\Region;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для Region
 */
class RegionFilter extends Region
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
        $query = Region::find();
        $regionTableName = Region::tableName();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->name && $query->andWhere(['LIKE', $regionTableName . '.name', $this->name]);
        $this->country_prefix && $query->andWhere([$regionTableName . '.country_prefix' => $this->country_prefix]);

        return $dataProvider;
    }
}
