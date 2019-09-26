<?php

namespace app\modules\nnp\filters;

use app\classes\grid\ActiveDataProvider;
use app\classes\traits\GetListTrait;
use app\modules\nnp\models\NumberExample;

/**
 * Фильтрация для NumberExamples
 */
class NumberExampleFilter extends NumberExample
{
    public $full_number_mask = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['operator_name', 'region_name', 'city_name', 'full_number_mask'], 'string'],
            [['country_code', 'prefix', 'ndc', 'ndc_type_id', 'operator_id', 'region_id', 'city_id', 'cnt'], 'integer'],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = NumberExample::find();
        $numberExampleTableName = NumberExample::tableName();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'db' => NumberExample::getDb(),
        ]);

        $this->country_code && $query->andWhere([$numberExampleTableName . '.country_code' => $this->country_code]);
        $this->ndc && $query->andWhere([$numberExampleTableName . '.ndc' => $this->ndc]);

        switch ($this->operator_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($numberExampleTableName . '.operator_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberExampleTableName . '.operator_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$numberExampleTableName . '.operator_id' => $this->operator_id]);
                break;
        }

        switch ($this->ndc_type_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($numberExampleTableName . '.ndc_type_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberExampleTableName . '.ndc_type_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$numberExampleTableName . '.ndc_type_id' => $this->ndc_type_id]);
                break;
        }

        switch ($this->region_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($numberExampleTableName . '.region_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberExampleTableName . '.region_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$numberExampleTableName . '.region_id' => $this->region_id]);
                break;
        }

        switch ($this->city_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($numberExampleTableName . '.city_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberExampleTableName . '.city_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$numberExampleTableName . '.city_id' => $this->city_id]);
                break;
        }

        $this->operator_name && $query->andWhere(['LIKE', $numberExampleTableName . '.operator_name', $this->operator_name]);
        $this->region_name && $query->andWhere(['LIKE', $numberExampleTableName . '.region_name', $this->region_name]);
        $this->city_name && $query->andWhere(['LIKE', $numberExampleTableName . '.city_name', $this->city_name]);

        if ($this->full_number_mask) {
            $this->full_number_mask = strtr($this->full_number_mask, ['.' => '_', '*' => '%']);
            $query->andWhere($numberExampleTableName . '.full_number::VARCHAR LIKE :full_number_mask', [':full_number_mask' => $this->full_number_mask]);
        }

        if ($this->prefix) {
            $query->andWhere(['prefix' => $this->prefix]);
        }

        return $dataProvider;
    }
}
