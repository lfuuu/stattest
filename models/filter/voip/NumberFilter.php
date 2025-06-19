<?php

namespace app\models\filter\voip;

use app\classes\traits\GetListTrait;
use app\models\Number;
use app\models\voip\Registry;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use app\modules\nnp\models\Operator;

/**
 * Фильтрация для Number
 */
class NumberFilter extends Number
{
    const ROWS_PER_PAGE = 100;

    const ACTION_SET_STATUS = 'set-status';
    const ACTION_SET_BEAUTY_LEVEL = 'set-beauty-level';
    const ACTION_SET_DID_GROUP = 'set-did-group';
    const ACTION_DELETE_NUMBERS = 'delete-numbers';

    public $number = '';
    public $number_from = '';
    public $number_to = '';
    public $city_id = '';
    public $region = '';
    public $status = '';
    public $source = '';
    public $did_group_id = '';
    public $beauty_level = '';
    public $original_beauty_level = '';
    public $usage_id = '';
    public $client_id = '';
    public $country_id = '';
    public $ndc_type_id = '';
    public $imsi = '';
    public $nnp_operator_id = '';
    public $orig_nnp_operator_id = '';

    public $unique_calls_per_month_3_from = '';
    public $unique_calls_per_month_3_to = '';

    public $unique_calls_per_month_2_from = '';
    public $unique_calls_per_month_2_to = '';

    public $unique_calls_per_month_1_from = '';
    public $unique_calls_per_month_1_to = '';

    public $unique_calls_per_month_0_from = '';
    public $unique_calls_per_month_0_to = '';

    public $number_tech = '';
    public $iccid = '';

    public $solution_date = '';
    public $solution_number = '';
    public $numbers_count_from = '';
    public $numbers_count_to = '';
    public $registry_number_from = '';
    public $registry_id = '';
    public $mvno_partner_id = '';
    public $is_with_discount = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['number', 'number_from', 'number_to', 'status', 'number_tech', 'source', 'solution_date', 'solution_number', 'registry_number_from'], 'string'],
            [['imsi', 'registry_id'], 'integer'],
            [['city_id', 'region', 'beauty_level', 'original_beauty_level', 'usage_id', 'client_id', 'country_id', 'ndc_type_id', 'mvno_partner_id', 'did_group_id', 'nnp_operator_id', 'orig_nnp_operator_id', 'is_with_discount'], 'integer'],
            [['unique_calls_per_month_3_from', 'unique_calls_per_month_3_to'], 'integer'],
            [['unique_calls_per_month_2_from', 'unique_calls_per_month_2_to'], 'integer'],
            [['unique_calls_per_month_1_from', 'unique_calls_per_month_1_to'], 'integer'],
            [['unique_calls_per_month_0_from', 'unique_calls_per_month_0_to'], 'integer'],
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
        $query = Number::find()
            ->alias('n');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => self::ROWS_PER_PAGE,
            ],
        ]);

        $query
            ->with('registry')
            ->with('didGroup')
            ->with('country')
            ->with('nnpOperator')
            ->with('origNnpOperator')
            ->with('clientAccount')
            ->with('usage')
        ;

        $query->joinWith('registry r');

        if ($this->number && !$this->number_from) {
            $this->number_from = $this->number;
        }

        $this->number = '';

        if ($this->number_from) {
            if ($this->number_to === '') {
                if (Number::find()->where(['number' => $this->number_from])->exists()) {
                    $query->andWhere(['n.number' => $this->number_from]);
                } else {
                    $query->andWhere(['LIKE', 'n.number', $this->number_from]);
                }
            } else {
                $query->andWhere(['between', 'n.number', $this->number_from, $this->number_to]);
            }
        }

        switch ($this->city_id) {
            case '':
                break;

            case GetListTrait::$isNull:
                $query->andWhere(['n.city_id' => null]);
                break;

            case GetListTrait::$isNotNull:
                $query->andWhere(['IS NOT', 'n.city_id', null]);
                break;

            default:
                $query->andWhere(['n.city_id' => $this->city_id]);
                break;
        }

        switch ($this->region) {
            case '':
                break;

            case GetListTrait::$isNull:
                $query->andWhere(['n.region' => null]);
                break;

            case GetListTrait::$isNotNull:
                $query->andWhere(['IS NOT', 'n.region', null]);
                break;

            default:
                $query->andWhere(['n.region' => $this->region]);
                break;
        }

        switch ($this->registry_id) {
            case '':
                break;

            case GetListTrait::$isNull:
                $query->andWhere(['registry_id' => null]);
                break;

            case GetListTrait::$isNotNull:
                $query->andWhere(['IS NOT', 'registry_id', null]);
                break;

            default:
                $query->andWhere(['registry_id' => $this->registry_id]);
                break;
        }

        switch ($this->mvno_partner_id) {
            case '':
                break;

            case GetListTrait::$isNull:
                $query->andWhere(['n.mvno_partner_id' => null]);
                break;

            case GetListTrait::$isNotNull:
                $query->andWhere(['IS NOT', 'n.mvno_partner_id', null]);
                break;

            default:
                $query->andWhere(['n.mvno_partner_id' => $this->mvno_partner_id]);
                break;
        }

        $this->status !== '' && $query->andWhere(['n.status' => $this->status]);
        $this->beauty_level !== '' && $query->andWhere(['n.beauty_level' => $this->beauty_level]);
        $this->original_beauty_level !== '' && $query->andWhere(['n.original_beauty_level' => $this->original_beauty_level]);
        $this->did_group_id !== '' && $query->andWhere(['n.did_group_id' => $this->did_group_id]);
        $this->ndc_type_id !== '' && $query->andWhere(['n.ndc_type_id' => $this->ndc_type_id]);
        $this->number_tech !== '' && $query->andWhere(['n.number_tech' => $this->number_tech]);

        $this->unique_calls_per_month_3_from !== '' && $query->andWhere(['>=', 'n.unique_calls_per_month_3', $this->unique_calls_per_month_3_from]);
        $this->unique_calls_per_month_3_to !== '' && $query->andWhere(['<=', 'n.unique_calls_per_month_3', $this->unique_calls_per_month_3_to]);

        $this->unique_calls_per_month_2_from !== '' && $query->andWhere(['>=', 'n.unique_calls_per_month_2', $this->unique_calls_per_month_2_from]);
        $this->unique_calls_per_month_2_to !== '' && $query->andWhere(['<=', 'n.unique_calls_per_month_2', $this->unique_calls_per_month_2_to]);

        $this->unique_calls_per_month_1_from !== '' && $query->andWhere(['>=', 'n.unique_calls_per_month_1', $this->unique_calls_per_month_1_from]);
        $this->unique_calls_per_month_1_to !== '' && $query->andWhere(['<=', 'n.unique_calls_per_month_1', $this->unique_calls_per_month_1_to]);

        $this->unique_calls_per_month_0_from !== '' && $query->andWhere(['>=', 'n.unique_calls_per_month_0', $this->unique_calls_per_month_0_from]);
        $this->unique_calls_per_month_0_to !== '' && $query->andWhere(['<=', 'n.unique_calls_per_month_0', $this->unique_calls_per_month_0_to]);

        $this->solution_number !== '' && $query->andWhere(['r.solution_number' => $this->solution_number]);

        $this->registry_number_from !== '' && $query->andWhere(['r.number_full_from' => $this->registry_number_from]);
        $this->solution_date !== '' && $query->andWhere(['r.solution_date' => $this->solution_date]);

        $this->country_id !== '' && $query->andWhere(['n.country_code' => $this->country_id]);
        $query->andFilterWhere(['n.source' => $this->source]);

        $this->nnp_operator_id !== '' && $query->andWhere(['n.nnp_operator_id' => $this->nnp_operator_id]);
        $this->orig_nnp_operator_id !== '' && $query->andWhere(['n.orig_nnp_operator_id' => $this->orig_nnp_operator_id]);
        $this->is_with_discount !== '' && $query->andWhere(['n.is_with_discount' => $this->is_with_discount, 'status' => Number::STATUS_INSTOCK]);

        switch ($this->imsi) {
            case GetListTrait::$isNull:
                $query->andWhere('n.imsi IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere('n.imsi IS NOT NULL');
                break;
            default:
                break;
        }

        switch ($this->usage_id) {
            case GetListTrait::$isNull:
                $query->andWhere('n.usage_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere('n.usage_id IS NOT NULL');
                break;
            default:
                break;
        }

        switch ($this->client_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere('n.client_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere('n.client_id IS NOT NULL');
                break;
            default:
                $query->andWhere(['n.client_id' => $this->client_id]);
                break;
        }

        return $dataProvider;
    }
}
