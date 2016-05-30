<?php

namespace app\modules\nnp\filter;

use app\classes\traits\GetListTrait;
use app\modules\nnp\models\NumberRange;
use app\modules\nnp\models\NumberRangePrefix;
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

    public $prefix_id = '';

    public function rules()
    {
        return [
            [['operator_source', 'region_source'], 'string'],
            [['country_code', 'ndc', 'number_from', 'is_mob', 'is_active', 'operator_id', 'region_id', 'city_id', 'is_reverse_city_id', 'prefix_id'], 'integer'],
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
        $numberRangeTableName = NumberRange::tableName();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->country_code && $query->andWhere([$numberRangeTableName . '.country_code' => $this->country_code]);
        $this->ndc && $query->andWhere([$numberRangeTableName . '.ndc' => $this->ndc]);

        $this->is_mob !== '' && $query->andWhere([$numberRangeTableName . '.is_mob' => (bool)$this->is_mob]);
        $this->is_active !== '' && $query->andWhere([$numberRangeTableName . '.is_active' => (bool)$this->is_active]);

        switch ($this->operator_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($numberRangeTableName . '.operator_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberRangeTableName . '.operator_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$numberRangeTableName . '.operator_id' => $this->operator_id]);
                break;
        }

        switch ($this->region_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($numberRangeTableName . '.region_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberRangeTableName . '.region_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$numberRangeTableName . '.region_id' => $this->region_id]);
                break;
        }

        switch ($this->city_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($numberRangeTableName . '.city_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberRangeTableName . '.city_id IS NOT NULL');
                break;
            default:
                if ($this->is_reverse_city_id) {
                    $query->andWhere([
                        'OR',
                        $numberRangeTableName . '.city_id IS NULL',
                        ['!=', $numberRangeTableName . '.city_id', $this->city_id]
                    ]);
                } else {
                    $query->andWhere([$numberRangeTableName . '.city_id' => $this->city_id]);
                }
                break;
        }

        $this->operator_source && $query->andWhere(['LIKE', $numberRangeTableName . '.operator_source', $this->operator_source]);
        $this->region_source && $query->andWhere(['LIKE', $numberRangeTableName . '.region_source', $this->region_source]);

        if ($this->number_from) {
            $query->andWhere(['<=', $numberRangeTableName . '.number_from', $this->number_from]);
            $query->andWhere(['>=', $numberRangeTableName . '.number_to', $this->number_from]);
        }

        $this->numbers_count_from && $query->andWhere('1 + ' . $numberRangeTableName . '.number_to - ' . $numberRangeTableName . '.number_from >= :numbers_count_from', [':numbers_count_from' => $this->numbers_count_from]);
        $this->numbers_count_to && $query->andWhere('1 + ' . $numberRangeTableName . '.number_to - ' . $numberRangeTableName . '.number_from <= :numbers_count_to', [':numbers_count_to' => $this->numbers_count_to]);

        if ($this->prefix_id) {
            $query->joinWith('numberRangePrefixes');
            $query->andWhere([NumberRangePrefix::tableName() . '.prefix_id' => $this->prefix_id]);
        }

        return $dataProvider;
    }
}
