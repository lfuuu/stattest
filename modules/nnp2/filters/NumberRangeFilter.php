<?php

namespace app\modules\nnp2\filters;

use app\classes\grid\ActiveDataProvider;
use app\classes\traits\GetListTrait;
use app\modules\nnp2\models\GeoPlace;
use app\modules\nnp2\models\NumberRange;
use Yii;
use yii\caching\Cache;
use yii\db\ActiveQuery;

/**
 * Фильтрация для NumberRange
 */
class NumberRangeFilter extends NumberRange
{
    //public $country_code = '';
    //public $ndc;
    public $ndc_str = '';
    //public $full_number_from = ''; // чтобы не изобретать новое поле, названо как существующее. Хотя фактически это full_number
    public $full_number_mask = '';
    public $region_id = '';
    public $city_id = '';
    public $numbers_count_from = '';
    public $numbers_count_to = '';
    //public $insert_time = ''; // чтобы не изобретать новое поле, названо как существующее. Хотя фактически это месяц добавления (insert_time) ИЛИ выключения (allocation_date_stop)
    public $allocation_date_start_from = '';
    public $allocation_date_start_to = '';

    public $sort;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['operator_source', 'ndc_str', 'region_source', 'city_source', 'full_number_from', 'insert_time', 'full_number_mask'], 'string'],
            [['country_code', 'ndc_type_id', 'is_active', 'is_valid', 'operator_id', 'region_id', 'city_id'], 'integer'],
            [['numbers_count_from', 'numbers_count_to'], 'integer'],
            [['allocation_date_start_from', 'allocation_date_start_to'], 'string'],
        ];
    }

    /**
     * Сбрасывает кэш Yii (общее поличество элементов в GridView)
     *
     * @param string $id
     * @throws \Exception
     */
    public function actionClearCache($id='cache')
    {
        Yii::$app->db->schema->refresh();

        if (!isset(Yii::$app->{$id}) || !(Yii::$app->{$id} instanceof Cache)) {
            $msg = Yii::t('Invalid cache to flush: {cache}', ['cache'=>$id]);
            throw new \Exception($msg);
        }

        /* @var $cache \yii\caching\Cache */
        $cache = Yii::$app->{$id};
        if ($cache->flush()) {
            $msg = Yii::t('app', 'Successfully flushed cache `{cache}`', ['cache'=>$id]);
            //Yii::$app->session->setFlash('success', $msg);
        } else {
            $msg = Yii::t('app', 'Problem while flushing cache `{cache}`', ['cache'=>$id]);
            //Yii::$app->session->setFlash('danger', $msg);
        }
    }

    protected function getSortParams(ActiveQuery $query)
    {
        if ($this->sort) {
            $sortField = ltrim($this->sort, '-');

            if (in_array($sortField, ['ndc_str', 'geo_place_id', 'geoPlace.parent_id'])) {
                $query->joinWith('geoPlace');
            }

            if ($sortField == 'region_id') {
                $query->joinWith('geoPlace.region');
            }

            if ($sortField == 'city_id') {
                $query->joinWith('geoPlace.city');
            }

            if ($sortField == 'geo_place_id') {
                $query->joinWith('geoPlace.region');
                $query->joinWith('geoPlace.city');
            }
        } else {
            $query->addOrderBy(['id' => SORT_ASC]);
        }

        return [
            'attributes' => [
                'full_number_from',
                'geo_place_id' => [
                    'asc' => [
                        'geo_place.ndc' => SORT_ASC,
                        'region.name' => SORT_ASC,
                        'city.name' => SORT_ASC,
                    ],
                    'desc' => [
                        'geo_place.ndc' => SORT_DESC,
                        'region.name' => SORT_DESC,
                        'city.name' => SORT_DESC,
                    ],
                ],
                'geoPlace.parent_id' => [
                    'asc' => [
                        'geo_place.parent_id' => SORT_ASC,
                    ],
                    'desc' => [
                        'geo_place.parent_id' => SORT_DESC,
                    ],
                ],
                'country_code',
                'ndc_str' => [
                    'asc' => [
                        'geo_place.ndc' => SORT_ASC,
                    ],
                    'desc' => [
                        'geo_place.ndc' => SORT_DESC,
                    ],
                ],
                'region_id' => [
                    'asc' => [
                        'region.name' => SORT_ASC,
                        'region.id' => SORT_ASC,
                    ],
                    'desc' => [
                        'region.name' => SORT_DESC,
                        'region.id' => SORT_DESC,
                    ],
                ],
                'city_id' => [
                    'asc' => [
                        'city.name' => SORT_ASC,
                        'city.id' => SORT_ASC,
                    ],
                    'desc' => [
                        'city.name' => SORT_DESC,
                        'city.id' => SORT_DESC,
                    ],
                ],
                'ndc_type_id',
                'operator_id',
                'is_active',
                'is_valid',
                'allocation_date_start',
                'insert_time',
            ]
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $this->actionClearCache();

        $query = self::find();

        $currentTableName = self::tableName();
        $geoTableName = GeoPlace::tableName();

        $sortParams = $this->getSortParams($query);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'db' => self::getDb(),
            'sort' => $sortParams,
        ]);

        $this->country_code && $query->andWhere([$currentTableName . '.country_code' => $this->country_code]);

        if ($this->ndc_str) {
            $query->joinWith('geoPlace');
            $query->andWhere([$geoTableName . '.ndc' => $this->ndc_str]);
        }

        if ($this->ndc) {
            $query->joinWith('geoPlace');
            $query->andWhere([$geoTableName . '.ndc' => $this->ndc]);
        }

        if (
            ($this->is_active !== '')
            && !is_null($this->is_active)
        ) {
            $query->andWhere([$currentTableName . '.is_active' => (bool)$this->is_active]);
        }
        if (
            ($this->is_valid !== '')
            && !is_null($this->is_valid)
        ) {
            $query->andWhere([$currentTableName . '.is_valid' => (bool)$this->is_valid]);
        }

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
                $query->andWhere($geoTableName . '.region_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($geoTableName . '.region_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$geoTableName . '.region_id' => $this->region_id]);
                break;
        }

        switch ($this->city_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($geoTableName . '.city_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($geoTableName . '.city_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$geoTableName . '.city_id' => $this->city_id]);
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
                ["DATE_TRUNC('month', {$currentTableName}.allocation_date_stop)::date" => $this->insert_time . '-01']
            ]);
        }

        $this->numbers_count_from && $query->andWhere('1 + ' . $currentTableName . '.number_to - ' . $currentTableName . '.number_from >= :numbers_count_from', [':numbers_count_from' => $this->numbers_count_from]);
        $this->numbers_count_to && $query->andWhere('1 + ' . $currentTableName . '.number_to - ' . $currentTableName . '.number_from <= :numbers_count_to', [':numbers_count_to' => $this->numbers_count_to]);

        $this->allocation_date_start_from && $query->andWhere(['>=', $currentTableName . '.allocation_date_start', $this->allocation_date_start_from]);
        $this->allocation_date_start_to && $query->andWhere(['<=', $currentTableName . '.allocation_date_start', $this->allocation_date_start_to]);

        return $dataProvider;
    }
}
