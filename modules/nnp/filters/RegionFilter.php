<?php

namespace app\modules\nnp\filters;

use app\modules\nnp\models\Region;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для Region
 */
class RegionFilter extends Region
{
    public $id = '';
    public $name = '';
    public $name_translit = '';
    public $country_code = '';
    public $parent_id = '';
    public $cnt_from = '';
    public $cnt_to = '';
    public $cnt_active_from = '';
    public $cnt_active_to = '';
    public $is_valid = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'name_translit', 'iso'], 'string'],
            [['id', 'country_code', 'parent_id', 'cnt_from', 'cnt_to', 'cnt_active_from', 'cnt_active_to', 'is_valid'], 'integer'],
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
        $this->name_translit && $query->andWhere(['LIKE', $regionTableName . '.name_translit', $this->name_translit]);
        $this->id && $query->andWhere([$regionTableName . '.id' => $this->id]);
        $this->country_code && $query->andWhere([$regionTableName . '.country_code' => $this->country_code]);
        $this->parent_id && $query->andWhere([$regionTableName . '.parent_id' => $this->parent_id]);

        $this->cnt_from !== '' && $query->andWhere(['>=', $regionTableName . '.cnt', $this->cnt_from]);
        $this->cnt_to !== '' && $query->andWhere(['<=', $regionTableName . '.cnt', $this->cnt_to]);

        $this->cnt_active_from !== '' && $query->andWhere(['>=', $regionTableName . '.cnt_active', $this->cnt_active_from]);
        $this->cnt_active_to !== '' && $query->andWhere(['<=', $regionTableName . '.cnt_active', $this->cnt_active_to]);
        $this->is_valid !== '' && $query->andWhere(["{$regionTableName}.is_valid" => (bool)$this->is_valid]);

        return $dataProvider;
    }
}
