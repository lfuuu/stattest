<?php

namespace app\models\filter;

use app\classes\grid\column\universal\CountryColumn;
use app\modules\nnp\column\RegionColumn;
use app\classes\grid\column\billing\DisconnectCauseColumn;
use app\classes\grid\column\billing\GeoColumn;
use app\classes\grid\column\billing\MobColumn;
use app\classes\grid\column\billing\OrigColumn;
use app\classes\grid\column\universal\AccountVersionColumn;
use app\classes\grid\column\universal\DateRangeDoubleColumn;
use app\modules\nnp\column\CityColumn;
use app\classes\grid\column\billing\ServerColumn;
use app\classes\grid\column\billing\TrunkColumn;
use app\classes\grid\column\billing\TrunkSuperClientColumn;
use app\classes\grid\column\universal\FloatRangeColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\UsageTrunkColumn;
use app\classes\grid\column\billing\DestinationColumn;
use app\helpers\DateTimeZoneHelper;
use app\models\billing\CallsRaw;
use app\models\UsageTrunk;
use app\modules\nnp\column\OperatorColumn;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\BaseInflector;
use yii\helpers\StringHelper;

/**
 * Фильтрация для CallsFilter
 */
class CallsRawFilter extends CallsRaw
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

    public $stats_nnp_package_minute_id_from = '';
    public $stats_nnp_package_minute_id_to = '';

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

    public $account_version = '';

    public $nnp_operator_id = '';

    public $nnp_region_id = '';

    public $nnp_city_id = '';

    public $nnp_country_prefix = '';

    public $nnp_ndc = '';

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

    public $disconnect_cause;

    public $timezone = DateTimeZoneHelper::TIMEZONE_UTC;

    public $is_full_report = 1;

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
            [['stats_nnp_package_minute_id_from', 'stats_nnp_package_minute_id_to'], 'integer'],
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
            [['account_version'], 'integer'],
            [['nnp_operator_id'], 'integer'],
            [['nnp_region_id'], 'integer'],
            [['nnp_city_id'], 'integer'],
            [['nnp_country_prefix'], 'integer'],
            [['nnp_ndc'], 'integer'],

            [['calls_count_from', 'calls_count_to'], 'integer'],

            [['rate_with_interconnect_from', 'rate_with_interconnect_to'], 'double'],

            [['interconnect_cost_sum_from', 'interconnect_cost_sum_to'], 'double'],
            [['cost_with_interconnect_sum_from', 'cost_with_interconnect_sum_to'], 'double'],
            [['cost_sum_from', 'cost_sum_to'], 'double'],

            [['asr_from', 'asr_to'], 'integer'],
            [['acd_from', 'acd_to'], 'double'],

            [['billed_time_sum_from', 'billed_time_sum_to'], 'double'],

            [['disconnect_cause', 'is_full_report'], 'integer'],
            [['timezone'], 'string'],
        ];
    }

    /**
     * Вернуть имена полей
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'connect_time_from' => 'Время начала разговора с',
            'connect_time_to' => 'Время начала разговора по',
            'cost_from' => 'Стоимость без интерконнекта от',
            'trunk_ids' => 'Оператор (суперклиент)',
            'cost_to' => 'Стоимость без интерконнекта до',
            'billed_time_from' => 'Время начала разговора от',
            'billed_time_to' => 'Время начала разговора до',
            'interconnect_cost_from' => 'Стоимость интерконнекта от',
            'interconnect_cost_to' => 'Стоимость интерконнекта до',
            'rate_from' => 'Цена минуты без интерконнекта от',
            'rate_to' => 'Цена минуты без интерконнекта до',
            'interconnect_rate_from' => 'Цена минуты интерконнекта от',
            'interconnect_rate_to' => 'Цена минуты интерконнекта до',
            'country_prefix' => 'ННП-страна',
            'stats_nnp_package_minute_id_from' => 'Потрачено минут пакета от',
            'stats_nnp_package_minute_id_to' => 'Потрачено минут пакета до',
            'timezone' => 'Таймзона',
            'is_full_report' => 'Полный отчет',
            'src_number' => 'Номер А',
            'dst_number' => 'Номер Б',
        ]);
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

        return $loadResult;
    }

    /**
     * Фильтровать
     *
     * @param int $pageSize
     * @return \app\classes\grid\ActiveDataProvider
     * @throws \Exception
     */
    public function search($pageSize = self::PAGE_SIZE)
    {
        $query = CallsRaw::find();
        $dataProvider = new \app\classes\grid\ActiveDataProvider([
            'db' => CallsRaw::getDb(),
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);

        $query
            ->with('region')
            ->with('city')
            ->with('operator');

        if (!$this->trunk_id || !$this->connect_time_from || !$this->connect_time_to) {
            $query->where('false');
            return $dataProvider;
        }

        $this->id !== '' && $query->andWhere(['id' => $this->id]);

        $connectTimeFrom = (new \DateTime($this->connect_time_from, (new \DateTimeZone($this->timezone))))
            ->setTime(0,0,0)
            ->setTimezone(new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC));

        $connectTimeTo = (new \DateTime($this->connect_time_to, (new \DateTimeZone($this->timezone))))
            ->setTime(0,0,0)
            ->modify("+1 day")
            ->setTimezone(new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC));

        $this->connect_time_from !== '' && $query->andWhere(['>=', 'connect_time', $connectTimeFrom->format(DateTimeZoneHelper::DATETIME_FORMAT)]);
        $this->connect_time_to !== '' && $query->andWhere(['<', 'connect_time', $connectTimeTo->format(DateTimeZoneHelper::DATETIME_FORMAT)]);

        $this->billed_time_from !== '' && $query->andWhere(['>=', 'billed_time', $this->billed_time_from]);
        $this->billed_time_to !== '' && $query->andWhere(['<=', 'billed_time', $this->billed_time_to]);

        $this->src_number = strtr($this->src_number, ['.' => '_', '*' => '%']);
        $this->src_number !== '' && $query->andWhere('src_number::VARCHAR LIKE :src_number', [':src_number' => $this->src_number]);

        $this->dst_number = strtr($this->dst_number, ['.' => '_', '*' => '%']);
        $this->dst_number !== '' && $query->andWhere('dst_number::VARCHAR LIKE :dst_number', [':dst_number' => $this->dst_number]);

        $this->rate_from !== '' && $query->andWhere(['>=', 'rate', $this->rate_from]);
        $this->rate_to !== '' && $query->andWhere(['<=', 'rate', $this->rate_to]);

        $this->interconnect_rate_from !== '' && $query->andWhere(['>=', 'interconnect_rate', $this->interconnect_rate_from]);
        $this->interconnect_rate_to !== '' && $query->andWhere(['<=', 'interconnect_rate', $this->interconnect_rate_to]);

        $this->cost_from !== '' && $query->andWhere(['>=', 'cost', $this->cost_from]);
        $this->cost_to !== '' && $query->andWhere(['<=', 'cost', $this->cost_to]);

        $this->interconnect_cost_from !== '' && $query->andWhere(['>=', 'interconnect_cost', $this->interconnect_cost_from]);
        $this->interconnect_cost_to !== '' && $query->andWhere(['<=', 'interconnect_cost', $this->interconnect_cost_to]);

        $this->stats_nnp_package_minute_id_from !== '' && $query->andWhere(['>=', 'stats_nnp_package_minute_id', $this->stats_nnp_package_minute_id_from]);
        $this->stats_nnp_package_minute_id_to !== '' && $query->andWhere(['<=', 'stats_nnp_package_minute_id', $this->stats_nnp_package_minute_id_to]);

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

        $this->account_version !== '' && $query->andWhere(['account_version' => $this->account_version]);

        $this->nnp_operator_id !== '' && $query->andWhere(['nnp_operator_id' => $this->nnp_operator_id]);

        $this->nnp_region_id !== '' && $query->andWhere(['nnp_region_id' => $this->nnp_region_id]);

        $this->nnp_city_id !== '' && $query->andWhere(['nnp_city_id' => $this->nnp_city_id]);

        $this->nnp_country_prefix !== '' && $query->andWhere(['nnp_country_prefix' => $this->nnp_country_prefix]);

        $this->nnp_ndc !== '' && $query->andWhere(['nnp_ndc' => $this->nnp_ndc]);

        (int)$this->disconnect_cause && $query->andWhere(['disconnect_cause' => $this->disconnect_cause]);

        !$this->isFilteringPossible() && $query->andWhere(new Expression('false'));

        // having
        $this->calls_count_from !== '' && $query->andHaving(['>=', 'COUNT(*)', (int)$this->calls_count_from]);
        $this->calls_count_to !== '' && $query->andHaving(['<=', 'COUNT(*)', (int)$this->calls_count_to]);

        $this->rate_with_interconnect_from !== '' && $query->andHaving([
            '>=',
            'AVG(rate + interconnect_rate)',
            (int)$this->rate_with_interconnect_from
        ]);
        $this->rate_with_interconnect_to !== '' && $query->andHaving([
            '<=',
            'AVG(rate + interconnect_rate)',
            (int)$this->rate_with_interconnect_to
        ]);

        $this->interconnect_cost_sum_from !== '' && $query->andHaving([
            '>=',
            'SUM(interconnect_cost)',
            (int)$this->interconnect_cost_sum_from
        ]);
        $this->interconnect_cost_sum_to !== '' && $query->andHaving([
            '<=',
            'SUM(interconnect_cost)',
            (int)$this->interconnect_cost_sum_to
        ]);

        $this->cost_with_interconnect_sum_from !== '' && $query->andHaving([
            '>=',
            'SUM(interconnect_cost)',
            (int)$this->cost_with_interconnect_sum_from
        ]);
        $this->cost_with_interconnect_sum_to !== '' && $query->andHaving([
            '<=',
            'SUM(interconnect_cost)',
            (int)$this->cost_with_interconnect_sum_to
        ]);

        $this->cost_sum_from !== '' && $query->andHaving(['>=', 'SUM(cost)', (int)$this->cost_sum_from]);
        $this->cost_sum_to !== '' && $query->andHaving(['<=', 'SUM(cost)', (int)$this->cost_sum_to]);

        $this->asr_from !== '' && $query->andHaving([
            '>=',
            '100.0 * SUM(CASE WHEN billed_time > 0 THEN 1 ELSE 0 END) / COUNT(*)',
            (int)$this->asr_from
        ]);
        $this->asr_to !== '' && $query->andHaving([
            '<=',
            '100.0 * SUM(CASE WHEN billed_time > 0 THEN 1 ELSE 0 END) / COUNT(*)',
            (int)$this->asr_to
        ]);

        $this->acd_from !== '' && $query->andHaving(['>=', 'SUM(billed_time) / COUNT(*)', (int)$this->acd_from]);
        $this->acd_to !== '' && $query->andHaving(['<=', 'SUM(billed_time) / COUNT(*)', (int)$this->acd_to]);

        $this->billed_time_sum_from !== '' && $query->andHaving([
            '>=',
            'SUM(billed_time)',
            (int)$this->billed_time_sum_from
        ]);
        $this->billed_time_sum_to !== '' && $query->andHaving([
            '<=',
            'SUM(billed_time)',
            (int)$this->billed_time_sum_to
        ]);

        return $dataProvider;
    }

    /**
     * Фильтровать для отчета по направлениям. Итого
     *
     * @return \app\classes\grid\ActiveDataProvider
     * @throws \Exception
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
                'rate_with_interconnect' => '1.0 * (rate + interconnect_rate)',
                // иначе интерпретируется, как строка, а не поля
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
        return $this->connect_time_from !== '';
    }

    /**
     * Вернуть колонки для GridView
     *
     * @return array
     */
    public function getColumns()
    {
        $columns = [
            [
                'attribute' => 'id',
                'class' => StringColumn::class,
            ],
            [
                'attribute' => 'server_id',
                'class' => ServerColumn::class,
            ],
            [
                'attribute' => 'trunk_ids', // фейковое поле
                'label' => 'Оператор (суперклиент)',
                'class' => TrunkSuperClientColumn::class,
                'enableSorting' => false,
                'value' => function (CallsRaw $call) {
                    return $call->trunk_id;
                },
            ],
            [
                'attribute' => 'trunk_id',
                'class' => TrunkColumn::class,
                'filterByIds' => $this->trunkIdsIndexed,
                'filterByServerIds' => $this->server_id,
                'filterOptions' => [
                    'class' => $this->trunk_id ? 'alert-success' : 'alert-danger',
                    'title' => 'Фильтр зависит от Региона (точка подключения) и Оператора (суперклиента)',
                ],
            ],
            [
                'attribute' => 'trunk_service_id',
                'class' => UsageTrunkColumn::class,
                'trunkId' => $this->trunk_id,
                'filterOptions' => [
                    'title' => 'Фильтр зависит от Транка',
                ],
            ],
            [
                'attribute' => 'connect_time',
                'class' => DateRangeDoubleColumn::class,
                'filterOptions' => [
                    'class' => $this->connect_time_from ? 'alert-success' : 'alert-danger',
                    'title' => 'У первой даты время считается 00:00, у второй 23:59',
                ],
            ],
            [
                'attribute' => 'src_number',
                'label' => 'Номер А',
                'class' => StringColumn::class,
                'filterOptions' => [
                    'title' => 'Допустимы цифры, _ или . (одна любая цифра), % или * (любая последовательность цифр, в том числе пустая строка)',
                ],
            ],
            [
                'attribute' => 'dst_number',
                'label' => 'Номер Б',
                'class' => StringColumn::class,
                'filterOptions' => [
                    'title' => 'Допустимы цифры, _ или . (одна любая цифра), % или * (любая последовательность цифр, в том числе пустая строка)',
                ],
            ],
            [
                'attribute' => 'prefix',
                'class' => IntegerColumn::class,
            ],
            [
                'attribute' => 'billed_time',
                'class' => IntegerRangeColumn::class,
            ],
            [
                'attribute' => 'rate',
                'class' => FloatRangeColumn::class,
                'format' => ['decimal', 4],
            ],
            [
                'attribute' => 'interconnect_rate',
                'class' => FloatRangeColumn::class,
                'format' => ['decimal', 4],
            ],
            [
                'label' => 'Цена минуты с интерконнектом, ¤',
                'format' => ['decimal', 4],
                'value' => function (CallsRaw $calls) {
                    return $calls->rate + $calls->interconnect_rate;
                },
            ],
            [
                'attribute' => 'cost',
                'class' => FloatRangeColumn::class,
                'format' => ['decimal', 4],
            ],
            [
                'attribute' => 'interconnect_cost',
                'class' => FloatRangeColumn::class,
                'format' => ['decimal', 4],
            ],
            [
                'label' => 'Стоимость с интерконнектом, ¤',
                'format' => ['decimal', 4],
                'value' => function (CallsRaw $calls) {
                    return $calls->cost + $calls->interconnect_cost;
                },
            ],
            [
                'attribute' => 'destination_id',
                'class' => DestinationColumn::class,
                'filterByServerId' => $this->server_id,
                'filterOptions' => [
                    'title' => 'Фильтр зависит от Региона (точка подключения)',
                ],
            ],
            [
                'attribute' => 'geo_id',
                'class' => GeoColumn::class,
            ],
            [
                'attribute' => 'orig',
                'class' => OrigColumn::class,
            ],
            [
                'attribute' => 'mob',
                'class' => MobColumn::class,
            ],
            [
                'attribute' => 'account_id',
                'class' => StringColumn::class,
            ],
            [
                'attribute' => 'disconnect_cause',
                'class' => DisconnectCauseColumn::class,
            ],
            [
                'attribute' => 'nnp_country_prefix',
                'class' => CountryColumn::class,
                'indexBy' => 'prefix',
            ],
            [
                'attribute' => 'nnp_ndc',
                'class' => IntegerColumn::class,
            ],
            [
                'attribute' => 'account_version',
                'class' => AccountVersionColumn::class,
            ],
            [
                'attribute' => 'nnp_operator_id',
                'class' => OperatorColumn::class,
            ],
            [
                'attribute' => 'nnp_region_id',
                'class' => RegionColumn::class,
            ],
            [
                'attribute' => 'nnp_city_id',
                'class' => CityColumn::class,
            ],
            [
                'attribute' => 'stats_nnp_package_minute_id',
                'format' => 'html',
                'class' => IntegerRangeColumn::class,
                'value' => function (CallsRaw $calls) {
                    return $calls->stats_nnp_package_minute_id . '<br/>' .
                        ($calls->nnp_package_minute_id ? 'минуты' : '') .
                        ($calls->nnp_package_price_id ? 'прайс' : '') .
                        ($calls->nnp_package_pricelist_id ? 'прайслист' : '');
                },
            ],
        ];

        if (!$this->is_full_report) {
            $columns = array_filter($columns, function ($row)
            {
                return isset($row['attribute'])
                    && in_array($row['attribute'],
                        ['id', 'trunk_id', 'orig', 'connect_time', 'src_number', 'dst_number', 'billed_time', 'cost',
                            'rate', 'interconnect_cost', 'disconnect_cause']
                    );
            });
        }

        return $columns;
    }

}
