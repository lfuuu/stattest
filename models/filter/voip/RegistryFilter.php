<?php

namespace app\models\filter\voip;

use app\models\Country;
use app\models\voip\Registry;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для Country
 */
class RegistryFilter extends Country
{
    public $country_id = '';
    public $city_id = '';
    public $source = '';
    public $ndc_type_id = '';
    public $number_from = '';
    public $number_to = '';
    public $account_id = '';
    public $ndc = '';
    public $solution_date = '';
    public $solution_number = '';
    public $numbers_count_from = '';
    public $numbers_count_to = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['country_id'], 'integer'],
            [['city_id'], 'integer'],
            [['source'], 'string'],
            [['ndc_type_id'], 'integer'],
            [['ndc'], 'integer'],
            [['number_from'], 'integer'],
            [['number_to'], 'integer'],
            [['account_id', 'numbers_count_from', 'numbers_count_to'], 'integer'],
            [['solution_date', 'solution_number'], 'string']
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = Registry::find();

        // equal filter
        foreach (['country_id', 'city_id', 'source', 'ndc_type_id', 'account_id', 'ndc', 'solution_date'] as $field) {
            if ($this->{$field} !== '') {
                $query->andWhere([$field => $this->{$field}]);
            }
        }

        $this->number_from !== '' && $query->andWhere(['LIKE', 'number_from', $this->number_from]);
        $this->number_to !== '' && $query->andWhere(['LIKE', 'number_to', $this->number_to]);

        $this->solution_number !== '' && $query->andWhere(['solution_number' => $this->solution_number]);
        $this->numbers_count_from !== '' && $query->andWhere(['>=', 'numbers_count', $this->numbers_count_from]);
        $this->numbers_count_to !== '' && $query->andWhere(['<=', 'numbers_count', $this->numbers_count_to]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }
}
