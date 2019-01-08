<?php

namespace app\models\filter\voip;

use app\classes\traits\GetListTrait;
use app\models\Number;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для Number
 */
class NumberFilter extends Number
{
    const ROWS_PER_PAGE = 100;

    public $number = '';
    public $city_id = '';
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

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['number', 'status', 'number_tech', 'source'], 'string'],
            [['imsi'], 'integer'],
            [['city_id', 'beauty_level', 'usage_id', 'client_id', 'country_id', 'ndc_type_id'], 'integer'], // , 'did_group_id'
            [['calls_per_month_2_from', 'calls_per_month_2_to'], 'integer'],
            [['calls_per_month_1_from', 'calls_per_month_1_to'], 'integer'],
            [['calls_per_month_0_from', 'calls_per_month_0_to'], 'integer'],
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

        $this->number !== '' && $query->andWhere(['LIKE', $numberTableName . '.number', $this->number]);

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

        $this->country_id !== '' && $query->andWhere([$numberTableName . '.country_code' => $this->country_id]);
        $query->andFilterWhere([$numberTableName.'.source' => $this->source]);

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
