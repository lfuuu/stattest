<?php

namespace app\models\filter\voip;

use app\models\Country;
use app\models\Number;
use app\models\voip\Registry;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\Query;

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
            [['account_id'], 'integer'],
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
        foreach (['country_id', 'city_id', 'source', 'ndc_type_id', 'account_id', 'ndc'] as $field) {
            if ($this->{$field} !== '') {
                $query->andWhere([$field => $this->{$field}]);
            }
        }

        $this->number_from !== '' && $query->andWhere(['LIKE', 'number_from', $this->number_from]);
        $this->number_to !== '' && $query->andWhere(['LIKE', 'number_to', $this->number_to]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }
}
