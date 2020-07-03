<?php

namespace app\modules\nnp2\filters;

use app\classes\grid\ActiveDataProvider;
use app\classes\traits\GetListTrait;
use app\modules\nnp2\models\RangeShort;

/**
 * Фильтрация для RangeShort
 */
class RangeShortFilter extends RangeShort
{
    //public $number_from = ''; // чтобы не изобретать новое поле, названо как существующее. Хотя фактически это number
    public $full_number_mask = '';

    public $numbers_count_from = '';
    public $numbers_count_to = '';

    //public $insert_time = ''; // чтобы не изобретать новое поле, названо как существующее. Хотя фактически это месяц добавления (insert_time) ИЛИ выключения (allocation_date_stop)
    public $allocation_date_start_from = '';
    public $allocation_date_start_to = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['ndc', 'insert_time'], 'string'],
            [['country_code', 'ndc_type_id', 'operator_id', 'region_id', 'city_id'], 'integer'],
            [['numbers_count_from', 'numbers_count_to', 'full_number_from'], 'integer'],
            [['allocation_date_start_from', 'allocation_date_start_to'], 'string'],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = self::find();

        $currentTableName = self::tableName();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'db' => self::getDb(),
        ]);

        $this->country_code && $query->andWhere([$currentTableName . '.country_code' => $this->country_code]);

        $this->ndc && $query->andWhere(['LIKE', $currentTableName . '.ndc', $this->ndc]);
        //$this->ndc && $query->andWhere([$currentTableName . '.ndc' => $this->ndc]);

        switch ($this->operator_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($currentTableName . '.operator_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($currentTableName . '.operator_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$currentTableName . '.operator_id' => $this->operator_id]);
                break;
        }

        switch ($this->ndc_type_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($currentTableName . '.ndc_type_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($currentTableName . '.ndc_type_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$currentTableName . '.ndc_type_id' => $this->ndc_type_id]);
                break;
        }

        switch ($this->region_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($currentTableName . '.region_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($currentTableName . '.region_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$currentTableName . '.region_id' => $this->region_id]);
                break;
        }

        switch ($this->city_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($currentTableName . '.city_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($currentTableName . '.city_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$currentTableName . '.city_id' => $this->city_id]);
                break;
        }

        if ($this->full_number_from) {
            $query->andWhere(['<=', $currentTableName . '.full_number_from', $this->full_number_from]);
            $query->andWhere(['>=', $currentTableName . '.full_number_to', $this->full_number_from]);
        }

        if ($this->full_number_mask) {
            $this->full_number_mask = strtr($this->full_number_mask, ['.' => '_', '*' => '%']);
            $query->andWhere($currentTableName . '.full_number_from::VARCHAR LIKE :full_number_mask', [':full_number_mask' => $this->full_number_mask]);
        }

        if ($this->insert_time) {
            $query->andWhere([
                'OR',
                ["DATE_TRUNC('month', {$currentTableName}.insert_time)::date" => $this->insert_time . '-01'],
                ["DATE_TRUNC('month', {$currentTableName}.allocation_date_start)::date" => $this->insert_time . '-01']
            ]);
        }

        $this->numbers_count_from && $query->andWhere('1 + ' . $currentTableName . '.number_to - ' . $currentTableName . '.number_from >= :numbers_count_from', [':numbers_count_from' => $this->numbers_count_from]);
        $this->numbers_count_to && $query->andWhere('1 + ' . $currentTableName . '.number_to - ' . $currentTableName . '.number_from <= :numbers_count_to', [':numbers_count_to' => $this->numbers_count_to]);

        $this->allocation_date_start_from && $query->andWhere(['>=', $currentTableName . '.allocation_date_start', $this->allocation_date_start_from]);
        $this->allocation_date_start_to && $query->andWhere(['<=', $currentTableName . '.allocation_date_start', $this->allocation_date_start_to]);

        $sort = \Yii::$app->request->get('sort');
        if (!$sort) {
            $query->addOrderBy(['id' => SORT_ASC]);
        }

        return $dataProvider;
    }
}
