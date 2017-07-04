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
    public $country_code = '';
    public $cnt_from = '';
    public $cnt_to = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
            [['country_code', 'cnt_from', 'cnt_to'], 'integer'],
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
        $this->country_code && $query->andWhere([$regionTableName . '.country_code' => $this->country_code]);

        $this->cnt_from !== '' && $query->andWhere(['>=', $regionTableName . '.cnt', $this->cnt_from]);
        $this->cnt_to !== '' && $query->andWhere(['<=', $regionTableName . '.cnt', $this->cnt_to]);

        return $dataProvider;
    }
}
