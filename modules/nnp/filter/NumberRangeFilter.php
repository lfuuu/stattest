<?php

namespace app\modules\nnp\filter;

use app\modules\nnp\models\NumberRange;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для NumberRange
 */
class NumberRangeFilter extends NumberRange
{
    public $country_code = '';
    public $ndc = '';
    public $number_from = ''; // чтобы не изобретать новое поле, назвоно как существующее. Хотя фактически это number
    public $operator_source = '';
    public $operator_id = '';
    public $region_source = '';
    public $region_id = '';
    public $is_mob = '';
    public $is_active = '';

    public function rules()
    {
        return [
            [['operator_source', 'region_source'], 'string'],
            [['country_code', 'ndc', 'number_from', 'is_mob', 'is_active', 'operator_id', 'region_id'], 'integer'],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = NumberRange::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->country_code && $query->andWhere(['country_code' => $this->country_code]);
        $this->ndc && $query->andWhere(['ndc' => $this->ndc]);

        $this->is_mob !== '' && $query->andWhere(['is_mob' => $this->is_mob]);
        $this->is_active !== '' && $query->andWhere(['is_active' => $this->is_active]);

        $this->operator_id && $query->andWhere(['operator_id' => $this->operator_id]);
        $this->region_id && $query->andWhere(['region_id' => $this->region_id]);

        $this->operator_source && $query->andWhere(['LIKE', 'operator_source', $this->operator_source]);
        $this->region_source && $query->andWhere(['LIKE', 'region_source', $this->region_source]);

        if ($this->number_from) {
            $query->andWhere(['<=', 'number_from', $this->number_from]);
            $query->andWhere(['>=', 'number_to', $this->number_from]);
        }

        return $dataProvider;
    }
}
