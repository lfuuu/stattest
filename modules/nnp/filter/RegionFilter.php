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

    public function rules()
    {
        return [
            [['name'], 'string'],
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

        return $dataProvider;
    }
}
