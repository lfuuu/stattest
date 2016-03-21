<?php

namespace app\models\filter;

use app\models\billing\Calls;
use app\models\UsageTrunk;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

/**
 * Фильтрация для CallsFilter
 */
class CallsFilter extends Calls
{
    const PAGE_SIZE = 50;
    const PAGE_SIZE_COST = 1000;

    public $id = '';

    public $connect_time_from = '';
    public $connect_time_to = '';

    public $billed_time_from = '';
    public $billed_time_to = '';

    public $src_number = '';
    public $dst_number = '';

    public $rate_from = '';
    public $rate_to = '';

    public $interconnect_rate_from = '';
    public $interconnect_rate_to = '';

    public $cost_from = '';
    public $cost_to = '';

    public $interconnect_cost_from = '';
    public $interconnect_cost_to = '';

    public $destination_id = '';

    public $geo_id = '';
    public $geo_ids = ''; // через запятую
    public $geoIds = []; // массивом

    public $server_id = '';

    public $trunk_id = '';
    public $trunk_ids = ''; // trunk_id через запятую. Берется из суперклиента
    public $trunkIdsIndexed = []; // trunk_id[] массивом. Значения в ключе. Берется из суперклиента

    public $trunk_service_id = '';

    public $account_id = '';

    public $orig = '';

    public $mob = '';

    public $prefix = '';

    // having
    public $calls_count_from = '';
    public $calls_count_to = '';

    public $rate_with_interconnect_from = '';
    public $rate_with_interconnect_to = '';

    public $cost_with_interconnect_sum_from = '';
    public $cost_with_interconnect_sum_to = '';

    public $interconnect_cost_sum_from = '';
    public $interconnect_cost_sum_to = '';

    public $cost_sum_from = '';
    public $cost_sum_to = '';

    public $asr_from = '';
    public $asr_to = '';

    public $acd_from = '';
    public $acd_to = '';

    public $billed_time_sum_from = '';
    public $billed_time_sum_to = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['connect_time_from', 'connect_time_to'], 'string'],
            [['src_number', 'dst_number'], 'integer'],
            [['billed_time_from', 'billed_time_to'], 'integer'],
            [['rate_from', 'rate_to'], 'double'],
            [['interconnect_rate_from', 'interconnect_rate_to'], 'double'],
            [['cost_from', 'cost_to'], 'double'],
            [['interconnect_cost_from', 'interconnect_cost_to'], 'double'],
            [['destination_id'], 'integer'],
            [['geo_id'], 'integer'],
            [['geo_ids'], 'string'],
            [['server_id'], 'integer'],
            [['trunk_id', 'trunk_ids'], 'integer'],
            [['trunk_service_id'], 'integer'],
            [['account_id'], 'integer'],
            [['orig'], 'integer'],
            [['mob'], 'integer'],
            [['prefix'], 'integer'],

            [['calls_count_from', 'calls_count_to'], 'integer'],

            [['rate_with_interconnect_from', 'rate_with_interconnect_to'], 'double'],

            [['interconnect_cost_sum_from', 'interconnect_cost_sum_to'], 'double'],
            [['cost_with_interconnect_sum_from', 'cost_with_interconnect_sum_to'], 'double'],
            [['cost_sum_from', 'cost_sum_to'], 'double'],

            [['asr_from', 'asr_to'], 'integer'],
            [['acd_from', 'acd_to'], 'double'],

            [['billed_time_sum_from', 'billed_time_sum_to'], 'double'],
        ];
    }

    /**
     * Загрузка данных
     */
    public function load($data, $formName = null)
    {
        $loadResult = parent::load($data, $formName);

        if ($this->trunk_ids !== '') {
            $this->trunkIdsIndexed = array_flip(explode(',', $this->trunk_ids));
            if ($this->trunk_id !== '' && !isset($this->trunkIdsIndexed[$this->trunk_id])) {
                // поменяли фильтр суперклиенту - надо сбросить фильтр по транку
                $this->trunk_id = '';
            }
        }

        if ($this->trunk_id !== '' && $this->trunk_service_id !== ''
            && ($usageTrunk = UsageTrunk::findOne($this->trunk_service_id))
            && $usageTrunk->trunk_id != $this->trunk_id
        ) {
            // поменяли фильтр по транку - надо сбросить фильтр по услуге транка
            $this->trunk_service_id = '';
        }

        if ($this->geo_id !== '') {
            // установили фильтр по geo_id - надо сбросить фильтр по geo_ids
            $this->geo_ids = '';
        }

        if ($this->geo_ids !== '') {
            $this->geoIds = explode(',', $this->geo_ids);
        }

        $this->src_number && ($this->src_number = strtr($this->src_number, ['.' => '_', '*' => '%']));
        $this->dst_number && ($this->dst_number = strtr($this->dst_number, ['.' => '_', '*' => '%']));

        return $loadResult;
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search($pageSize = self::PAGE_SIZE)
    {
        $query = Calls::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);

        $this->id !== '' && $query->andWhere(['id' => $this->id]);

        $this->connect_time_from !== '' && $query->andWhere(['>=', 'connect_time', $this->connect_time_from . ' 00:00:00']);
        $this->connect_time_to !== '' && $query->andWhere(['<=', 'connect_time', $this->connect_time_to . ' 23:59:59']);

        $this->billed_time_from !== '' && $query->andWhere(['>=', 'billed_time', $this->billed_time_from]);
        $this->billed_time_to !== '' && $query->andWhere(['<=', 'billed_time', $this->billed_time_to]);

        $this->src_number !== '' && $query->andWhere('src_number::VARCHAR LIKE :src_number', [':src_number' => $this->src_number]);
        $this->dst_number !== '' && $query->andWhere('dst_number::VARCHAR LIKE :dst_number', [':dst_number' => $this->dst_number]);

        $this->rate_from !== '' && $query->andWhere(['>=', 'rate', $this->rate_from]);
        $this->rate_to !== '' && $query->andWhere(['<=', 'rate', $this->rate_to]);

        $this->interconnect_rate_from !== '' && $query->andWhere(['>=', 'interconnect_rate', $this->interconnect_rate_from]);
        $this->interconnect_rate_to !== '' && $query->andWhere(['<=', 'interconnect_rate', $this->interconnect_rate_to]);

        $this->cost_from !== '' && $query->andWhere(['>=', 'cost', $this->cost_from]);
        $this->cost_to !== '' && $query->andWhere(['<=', 'cost', $this->cost_to]);

        $this->interconnect_cost_from !== '' && $query->andWhere(['>=', 'interconnect_cost', $this->interconnect_cost_from]);
        $this->interconnect_cost_to !== '' && $query->andWhere(['<=', 'interconnect_cost', $this->interconnect_cost_to]);

        $this->destination_id !== '' && $query->andWhere(['destination_id' => $this->destination_id]);

        $this->geo_id && $query->andWhere(['geo_id' => $this->geo_id]);
        $this->geoIds && $query->andWhere(['IN', 'geo_id', $this->geoIds]);

        $this->server_id !== '' && $query->andWhere(['server_id' => $this->server_id]);

        $this->trunk_id !== '' && $query->andWhere(['trunk_id' => $this->trunk_id]);

        $this->trunk_service_id !== '' && $query->andWhere(['trunk_service_id' => $this->trunk_service_id]);

        $this->account_id !== '' && $query->andWhere(['account_id' => $this->account_id]);

        $this->orig !== '' && $query->andWhere(($this->orig ? '' : 'NOT ') . 'orig');
        $this->mob !== '' && $query->andWhere(($this->mob ? '' : 'NOT ') . 'mob');

        $this->prefix !== '' && $query->andWhere(['prefix' => $this->prefix]);

        !$this->isFilteringPossible() && $query->andWhere('false');

        // having
        $this->calls_count_from !== '' && $query->andHaving(['>=', 'COUNT(*)', (int)$this->calls_count_from]);
        $this->calls_count_to !== '' && $query->andHaving(['<=', 'COUNT(*)', (int)$this->calls_count_to]);

        $this->rate_with_interconnect_from !== '' && $query->andHaving(['>=', 'AVG(rate + interconnect_rate)', (int)$this->rate_with_interconnect_from]);
        $this->rate_with_interconnect_to !== '' && $query->andHaving(['<=', 'AVG(rate + interconnect_rate)', (int)$this->rate_with_interconnect_to]);

        $this->interconnect_cost_sum_from !== '' && $query->andHaving(['>=', 'SUM(interconnect_cost)', (int)$this->interconnect_cost_sum_from]);
        $this->interconnect_cost_sum_to !== '' && $query->andHaving(['<=', 'SUM(interconnect_cost)', (int)$this->interconnect_cost_sum_to]);

        $this->cost_with_interconnect_sum_from !== '' && $query->andHaving(['>=', 'SUM(interconnect_cost)', (int)$this->cost_with_interconnect_sum_from]);
        $this->cost_with_interconnect_sum_to !== '' && $query->andHaving(['<=', 'SUM(interconnect_cost)', (int)$this->cost_with_interconnect_sum_to]);

        $this->cost_sum_from !== '' && $query->andHaving(['>=', 'SUM(cost)', (int)$this->cost_sum_from]);
        $this->cost_sum_to !== '' && $query->andHaving(['<=', 'SUM(cost)', (int)$this->cost_sum_to]);

        $this->asr_from !== '' && $query->andHaving(['>=', '100.0 * SUM(CASE WHEN billed_time > 0 THEN 1 ELSE 0 END) / COUNT(*)', (int)$this->asr_from]);
        $this->asr_to !== '' && $query->andHaving(['<=', '100.0 * SUM(CASE WHEN billed_time > 0 THEN 1 ELSE 0 END) / COUNT(*)', (int)$this->asr_to]);

        $this->acd_from !== '' && $query->andHaving(['>=', 'SUM(billed_time) / COUNT(*)', (int)$this->acd_from]);
        $this->acd_to !== '' && $query->andHaving(['<=', 'SUM(billed_time) / COUNT(*)', (int)$this->acd_to]);

        $this->billed_time_sum_from !== '' && $query->andHaving(['>=', 'SUM(billed_time)', (int)$this->billed_time_sum_from]);
        $this->billed_time_sum_to !== '' && $query->andHaving(['<=', 'SUM(billed_time)', (int)$this->billed_time_sum_to]);

        return $dataProvider;
    }

    /**
     * Фильтровать для отчета по направлениям. Итого
     *
     * @return ActiveDataProvider
     */
    public function searchCostSummary()
    {
        $dataProvider = $this->search(self::PAGE_SIZE_COST);

        /** @var ActiveQuery $query */
        $query = $dataProvider->query;
        $query->select([
            // эти псевдо-поля надо не забыть определить в Calls
            'calls_count' => 'COUNT(*)',
            'billed_time_sum' => '1.0 * SUM(billed_time) / 60',
            'acd' => '1.0 * SUM(billed_time) / COUNT(billed_time > 0) / 60',

            'cost_sum' => 'SUM(cost)',
            'interconnect_cost_sum' => 'SUM(interconnect_cost)',
            'cost_with_interconnect_sum' => 'SUM(cost + interconnect_cost)',

            'asr' => '100.0 * SUM(CASE WHEN billed_time > 0 THEN 1 ELSE 0 END) / COUNT(*)',
        ]);
        return $dataProvider;
    }

    /**
     * Фильтровать для отчета по направлениям
     *
     * @return ActiveDataProvider
     */
    public function searchCost()
    {
        $dataProvider = $this->searchCostSummary();

        /** @var ActiveQuery $query */
        $query = $dataProvider->query;
        $query->select([
                'prefix',
                'rate',
                'interconnect_rate',
                'rate_with_interconnect' => '1.0 * (rate + interconnect_rate)', // иначе интерпретируется, как строка, а не поля
            ] + $query->select);
        $query->groupBy(['prefix', 'rate', 'interconnect_rate']);
        $query->orderBy(['CAST(prefix AS VARCHAR)' => SORT_ASC]); // prefix::VARCHAR почему-то не работает
        return $dataProvider;
    }

    /**
     * Указаны ли необходимые фильтры. Если нет, то фильтрация не происходит
     * @return bool
     */
    public function isFilteringPossible()
    {
        return $this->trunk_id !== '' && $this->connect_time_from !== '';
    }
}
