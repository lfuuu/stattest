<?php

namespace app\modules\nnp\filter;

use app\classes\traits\GetListTrait;
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
    public $numbers_count_from = '';
    public $numbers_count_to = '';
    public $city_id = '';
    public $is_reverse_city_id = '';

    public function rules()
    {
        return [
            [['operator_source', 'region_source'], 'string'],
            [['country_code', 'ndc', 'number_from', 'is_mob', 'is_active', 'operator_id', 'region_id', 'city_id', 'is_reverse_city_id'], 'integer'],
            [['numbers_count_from', 'numbers_count_to'], 'integer'],
        ];
    }

    /**
     * имена полей
     * @return [] [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return parent::attributeLabels() + [
            'is_reverse_city_id' => 'Кроме',
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

        switch ($this->operator_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere('operator_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere('operator_id IS NOT NULL');
                break;
            default:
                $query->andWhere(['operator_id' => $this->operator_id]);
                break;
        }

        switch ($this->region_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere('region_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere('region_id IS NOT NULL');
                break;
            default:
                $query->andWhere(['region_id' => $this->region_id]);
                break;
        }

        switch ($this->city_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere('city_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere('city_id IS NOT NULL');
                break;
            default:
                if ($this->is_reverse_city_id) {
                    $query->andWhere([
                        'OR',
                        'city_id IS NULL',
                        ['!=', 'city_id', $this->city_id]
                    ]);
                } else {
                    $query->andWhere(['city_id' => $this->city_id]);
                }
                break;
        }

        $this->operator_source && $query->andWhere(['LIKE', 'operator_source', $this->operator_source]);
        $this->region_source && $query->andWhere(['LIKE', 'region_source', $this->region_source]);

        if ($this->number_from) {
            $query->andWhere(['<=', 'number_from', $this->number_from]);
            $query->andWhere(['>=', 'number_to', $this->number_from]);
        }

        $this->numbers_count_from && $query->andWhere('1 + number_to - number_from >= :numbers_count_from', [':numbers_count_from' => $this->numbers_count_from]);
        $this->numbers_count_to && $query->andWhere('1 + number_to - number_from <= :numbers_count_to', [':numbers_count_to' => $this->numbers_count_to]);

        return $dataProvider;
    }
}
