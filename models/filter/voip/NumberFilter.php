<?php

namespace app\models\filter\voip;

use app\classes\traits\GetListTrait;
use app\models\Number;
use app\models\voip\Registry;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * Фильтрация для Number
 */
class NumberFilter extends Number
{
    const ROWS_PER_PAGE = 100;

    public $number = '';
    public $number_from = '';
    public $number_to = '';
    public $city_id = '';
    public $region = '';
    public $status = '';
    public $source = '';
    public $did_group_id = '';
    public $beauty_level = '';
    public $usage_id = '';
    public $client_id = '';
    public $country_id = '';
    public $ndc_type_id = '';
    public $imsi = '';

    public $calls_per_month_2_from = '';
    public $calls_per_month_2_to = '';

    public $calls_per_month_1_from = '';
    public $calls_per_month_1_to = '';

    public $calls_per_month_0_from = '';
    public $calls_per_month_0_to = '';

    public $number_tech = '';
    public $iccid = '';

    public $solution_date = '';
    public $solution_number = '';
    public $numbers_count_from = '';
    public $numbers_count_to = '';
    public $registry_number_from = '';
    public $with_registry = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['number', 'number_from', 'number_to', 'status', 'number_tech', 'source', 'solution_date', 'solution_number', 'registry_number_from'], 'string'],
            [['imsi', 'with_registry'], 'integer'],
            [['city_id', 'region', 'beauty_level', 'usage_id', 'client_id', 'country_id', 'ndc_type_id'], 'integer'], // , 'did_group_id'
            [['calls_per_month_2_from', 'calls_per_month_2_to'], 'integer'],
            [['calls_per_month_1_from', 'calls_per_month_1_to'], 'integer'],
            [['calls_per_month_0_from', 'calls_per_month_0_to'], 'integer'],
            [['numbers_count_from', 'numbers_count_to'], 'integer'],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = Number::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => self::ROWS_PER_PAGE,
            ],
        ]);

        $numberTableName = Number::tableName();
        $registryTableName = Registry::tableName();

        $query->with('registry')->with('didGroup')->with('country');

        if ($this->number && !$this->number_from) {
            $this->number_from = $this->number;
        }

        $this->number = '';

        if ($this->number_from) {
            if ($this->number_to === '') {
                $query->andWhere(['LIKE', $numberTableName . '.number', $this->number_from]);
            } else {
                $query->andWhere(['between', 'number', $this->number_from, $this->number_to]);
            }
        }

        switch ($this->city_id) {
            case '':
                break;

            case GetListTrait::$isNull:
                $query->andWhere(['city_id' => null]);
                break;

            case GetListTrait::$isNotNull:
                $query->andWhere(['IS NOT', 'city_id', null]);
                break;

            default:
                $query->andWhere([$numberTableName . '.city_id' => $this->city_id]);
                break;
        }

        switch ($this->region) {
            case '':
                break;

            case GetListTrait::$isNull:
                $query->andWhere(['region' => null]);
                break;

            case GetListTrait::$isNotNull:
                $query->andWhere(['IS NOT', 'region', null]);
                break;

            default:
                $query->andWhere([$numberTableName . '.region' => $this->region]);
                break;
        }

        $this->status !== '' && $query->andWhere([$numberTableName . '.status' => $this->status]);
        $this->beauty_level !== '' && $query->andWhere([$numberTableName . '.beauty_level' => $this->beauty_level]);
        $this->did_group_id !== '' && $query->andWhere([$numberTableName . '.did_group_id' => $this->did_group_id]);
        $this->ndc_type_id !== '' && $query->andWhere([$numberTableName . '.ndc_type_id' => $this->ndc_type_id]);
        $this->number_tech !== '' && $query->andWhere([$numberTableName . '.number_tech' => $this->number_tech]);

        $this->calls_per_month_2_from !== '' && $query->andWhere(['>=', $numberTableName . '.calls_per_month_2', $this->calls_per_month_2_from]);
        $this->calls_per_month_2_to !== '' && $query->andWhere(['<=', $numberTableName . '.calls_per_month_2', $this->calls_per_month_2_to]);

        $this->calls_per_month_1_from !== '' && $query->andWhere(['>=', $numberTableName . '.calls_per_month_1', $this->calls_per_month_1_from]);
        $this->calls_per_month_1_to !== '' && $query->andWhere(['<=', $numberTableName . '.calls_per_month_1', $this->calls_per_month_1_to]);

        $this->calls_per_month_0_from !== '' && $query->andWhere(['>=', $numberTableName . '.calls_per_month_0', $this->calls_per_month_0_from]);
        $this->calls_per_month_0_to !== '' && $query->andWhere(['<=', $numberTableName . '.calls_per_month_0', $this->calls_per_month_0_to]);

        $this->solution_number !== '' && $query->andWhere([$registryTableName . '.solution_number' => $this->solution_number]);

        $this->registry_number_from !== '' && $query->andWhere([$registryTableName . '.number_full_from' => $this->registry_number_from]);
        $this->solution_date !== '' && $query->andWhere([$registryTableName . '.solution_date' => $this->solution_date]);
        $this->with_registry !== '' && $query->andWhere($this->with_registry == -2 ? ['NOT', ['registry_id' => null]] : ['registry_id' => null]);

        $this->country_id !== '' && $query->andWhere([$numberTableName . '.country_code' => $this->country_id]);
        $query->andFilterWhere([$numberTableName . '.source' => $this->source]);

        switch ($this->imsi) {
            case GetListTrait::$isNull:
                $query->andWhere($numberTableName . '.imsi IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberTableName . '.imsi IS NOT NULL');
                break;
            default:
                break;
        }

        switch ($this->usage_id) {
            case GetListTrait::$isNull:
                $query->andWhere($numberTableName . '.usage_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberTableName . '.usage_id IS NOT NULL');
                break;
            default:
                break;
        }

        switch ($this->client_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($numberTableName . '.client_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberTableName . '.client_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$numberTableName . '.client_id' => $this->client_id]);
                break;
        }

        return $dataProvider;
    }
}
