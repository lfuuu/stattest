<?php
/**
 * Calls_raw report model
 */

namespace app\models\voip\filter;

use app\dao\CurrencyRateDao;
use app\helpers\DateTimeZoneHelper;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use app\models\billing\DisconnectCause;
use app\classes\yii\CTEQuery;
use yii\db\Query;

/**
 * Class Raw
 *
 * @property array $server_ids
 * @property string $connect_time_from
 * @property string $connect_time_to
 * @property string $correct_connect_time_to
 * @property string $session_time_from
 * @property string $session_time_to
 * @property bool $is_success_calls
 * @property array $src_routes_ids
 * @property array $dst_routes_ids
 * @property string $src_number
 * @property string $dst_number
 * @property array $src_operator_ids
 * @property array $dst_operator_ids
 * @property array $src_regions_ids
 * @property array $dst_regions_ids
 * @property array $src_cities_ids
 * @property array $dst_cities_ids
 * @property array $src_contracts_ids
 * @property array $dst_contracts_ids
 * @property array $disconnect_causes
 * @property array $src_countries_ids
 * @property array $dst_countries_ids
 * @property array $src_destinations_ids
 * @property array $dst_destinations_ids
 *
 * @property string $src_operator_name
 * @property string $dst_operator_name
 * @property string $src_region_name
 * @property string $dst_region_name
 * @property string $src_country_name
 * @property string $dst_country_name
 * @property string $src_city_name
 * @property string $dst_city_name
 *
 * @property array $group
 * @property array $aggr
 * @property string $group_period
 * @property string $sort
 */
class CallsRawFilter extends Model
{
    const UNATTAINABLE_SESSION_TIME = 2592000;

    public $groupConst = [
        'src_route' => 'Транк-оригинатор',
        'dst_route' => 'Транк-терминатор',
        'src_number' => 'Номер А',
        'dst_number' => 'Номер В',
        'src_operator_name' => 'Оператор номера А',
        'dst_operator_name' => 'Оператор номера В',
        'src_country_name' => 'Страна номера А',
        'dst_country_name' => 'Страна номера В',
        'src_region_name' => 'Регион номера А',
        'dst_region_name' => 'Регион номера В',
        'src_city_name' => 'Город номера А',
        'dst_city_name' => 'Город номера В',
    ];

    public $aggrConst = [
        'sale_sum' => 'SUM(@(sale))',
        'sale_avg' => 'AVG(@(sale))',
        'sale_min' => 'MIN(@(sale))',
        'sale_max' => 'MAX(@(sale))',
        'cost_price_sum' => 'SUM(cost_price)',
        'cost_price_avg' => 'AVG(cost_price)',
        'cost_price_min' => 'MIN(cost_price)',
        'cost_price_max' => 'MAX(cost_price)',
        'margin_sum' => 'SUM((@(sale)) - cost_price)',
        'margin_avg' => 'AVG((@(sale)) - cost_price)',
        'margin_min' => 'MIN((@(sale)) - cost_price)',
        'margin_max' => 'MAX((@(sale)) - cost_price)',
        'session_time_sum' => 'SUM(session_time)',
        'session_time_avg' => 'AVG(session_time)',
        'session_time_min' => 'MIN(session_time)',
        'session_time_max' => 'MAX(session_time)',
        'calls_count' => 'COUNT(connect_time)',
        'nonzero_calls_count' => 'SUM((CASE WHEN session_time > 0 THEN 1 ELSE 0 END))',
        'asd' => 'SUM(session_time) / SUM((CASE WHEN session_time > 0 THEN 1 ELSE 0 END))',
        'asr' => 'SUM((CASE WHEN session_time > 0 THEN 1 ELSE 0 END))::real / COUNT(connect_time)::real'
    ];

    public $aggrLabels = [
        'sale_sum' => 'Продажа: сумма',
        'sale_avg' => 'Продажа: средняя',
        'sale_min' => 'Продажа: минимальная',
        'sale_max' => 'Продажа: максимальная',
        'cost_price_sum' => 'Себестоимость: сумма',
        'cost_price_avg' => 'Себестоимость: средняя',
        'cost_price_min' => 'Себестоимость: минимальная',
        'cost_price_max' => 'Себестоимость: максимальная',
        'margin_sum' => 'Маржа: сумма',
        'margin_avg' => 'Маржа: средняя',
        'margin_min' => 'Маржа: минимальная',
        'margin_max' => 'Маржа: максимальная',
        'session_time_sum' => 'Длительность: сумма',
        'session_time_avg' => 'Длительность: средняя',
        'session_time_min' => 'Длительность: минимальная',
        'session_time_max' => 'Длительность: максимальная',
        'calls_count' => 'Количество звонков',
        'nonzero_calls_count' => 'Количество ненулевых звонков',
        'asd' => 'ASD',
        'asr' => 'ASR',
    ];

    public $server_ids = [];
    public $connect_time_from = null;
    public $connect_time_to = null;
    public $correct_connect_time_to = null;
    public $session_time_from = null;
    public $session_time_to = null;
    public $is_success_calls = null;
    public $src_routes_ids = [];
    public $dst_routes_ids = [];
    public $src_number = null;
    public $dst_number = null;
    public $src_operator_ids = [];
    public $dst_operator_ids = [];
    public $src_regions_ids = [];
    public $dst_regions_ids = [];
    public $src_cities_ids = [];
    public $dst_cities_ids = [];
    public $src_contracts_ids = [];
    public $dst_contracts_ids = [];
    public $disconnect_causes = [];
    public $src_countries_ids = [];
    public $dst_countries_ids = [];
    public $src_destinations_ids = [];
    public $dst_destinations_ids = [];

    public $src_operator_name = null;
    public $dst_operator_name = null;
    public $src_region_name = null;
    public $dst_region_name = null;
    public $src_country_name = null;
    public $dst_country_name = null;
    public $src_city_name = null;
    public $dst_city_name = null;
    public $session_time = null;

    public $group = [];

    public $aggr = [];

    public $group_period = '';

    public $sort = null;

    public $currency = 'RUB';
    public $currency_rate = 1;

    /**
     * Rules set
     *
     * @return array
     */
    public function rules()
    {
        return [
            [
                [
                    'is_success_calls',
                    'session_time_from',
                    'session_time_to',
                    'currency_rate',
                ],
                'integer'
            ],
            [
                [
                    'connect_time_from',
                    'connect_time_to',
                    'correct_connect_time_to',
                    'src_number',
                    'dst_number',
                    'group_period',
                    'currency',
                ],
                'string'
            ],
            [
                [
                    'server_ids',
                    'src_operator_ids',
                    'dst_operator_ids',
                    'src_regions_ids',
                    'dst_regions_ids',
                    'src_cities_ids',
                    'dst_cities_ids',
                    'disconnect_causes',
                    'src_countries_ids',
                    'dst_countries_ids',
                    'src_destinations_ids',
                    'dst_destinations_ids',
                    'dst_routes_ids',
                    'src_routes_ids',
                    'src_contracts_ids',
                    'dst_contracts_ids',
                ],
                'each',
                'rule' => ['integer']
            ],
            [
                [
                    'group',
                    'aggr',
                ],
                'each',
                'rule' => ['string']
            ]
        ];
    }

    /**
     * Custom data load
     *
     * @param array $get
     *
     * @return bool
     */
    public function load(array $get)
    {
        parent::load($get);

        $this->sort = Yii::$app->request->get('sort');

        /**
         * BETWEEN делать сравнение <= со вторым параметром,
         * поэтому в интервал могут попасть лишние звонки.
         * Чтобы этого не случилось отнимает 1 секунду.
         * +
         * Проверяем не составляет ли интервал больше одного месяца
         */
        if ($this->connect_time_from && $this->connect_time_to) {
            $dateStart = new \DateTime($this->connect_time_from);
            $dateEnd = new \DateTime($this->connect_time_to);
            $dateEnd->modify('-1 second');
            $interval = $dateEnd->diff($dateStart);
            if ($interval->m > 1 || ($interval->m && ($interval->i || $interval->d || $interval->h || $interval->s))) {
                Yii::$app->session->addFlash('error', 'Временной период больше одного месяца');
                return false;
            }

            $this->correct_connect_time_to = $dateEnd->format(DateTimeZoneHelper::DATETIME_FORMAT);
        }

        if (isset($this->currency) && $this->currency != 'RUB') {
            $this->currency_rate = CurrencyRateDao::findRate($this->currency, date(DateTimeZoneHelper::DATE_FORMAT))->getAttribute('rate');
        }

        if (!is_array($this->group)) {
            $this->group = [];
        }

        if (!is_array($this->aggr)) {
            $this->aggr = [];
        }

        return $this->validate();
    }

    /**
     * Возвращает выражение для пересчета денежных значений из calls_raw в рубли
     *
     * @param string $field
     *
     * @return string
     */
    public static function getMoneyCalculateExpression($field)
    {
        return "(CASE 
                   WHEN
                     c.currency IS NOT NULL AND c.currency != 'RUB'
                   THEN
                     $field * rate.rate
                   ELSE
                     $field
                  END)";
    }
    
    /**
     * Проверка на наличие обязательных фильтров
     *
     * @return bool
     */
    public function isFilteringPossible()
    {
        if ($this->connect_time_from) {
            $attributes = $this->getAttributes(
                null,
                [
                    'groupConst',
                    'aggrConst',
                    'aggrLabels',
                    'group',
                    'aggr',
                    'group_period',
                    'connect_time_to',
                    'connect_time_from',
                    'sort',
                    'currency',
                    'currency_rate',
                ]
            );
            foreach ($attributes as $value) {
                if ($value) {
                    return true;
                }
            }
        }

        return false;

    }

    /**
     * Отчет по calls_raw (живет по адресу /voip/cdr)
     *
     * @return ActiveDataProvider|ArrayDataProvider
     */
    public function getReport()
    {
        if (!$this->isFilteringPossible()) {
            return new ArrayDataProvider(
                [
                    'allModels' => [],
                ]
            );
        }

        $query1 = new Query();
        $query2 = new Query();
        $query3 = new Query();
        $query4 = new CTEQuery();

        $query1
            ->select(
                [
                    'cr.cdr_id',
                    'cr.connect_time',
                    'cr.disconnect_cause',
                    't.name src_route',
                    'src_number' => new Expression('cr.src_number::varchar'),
                    'dst_number' => new Expression('cr.dst_number::varchar'),
                    'cr.pdd',
                    'o.name src_operator_name',
                    'nc.name_rus src_country_name',
                    'r.name src_region_name',
                    'ci.name src_city_name',
                    'st.contract_number || \' (\' || cct.name || \')\' src_contract_name',
                    'sale' => new Expression(self::getMoneyCalculateExpression('@(cr.cost)')),
                    'orig_rate' => new Expression(self::getMoneyCalculateExpression('cr.rate')),
                ]
            )
            ->from('calls_raw.calls_raw cr')
            ->leftJoin('auth.trunk t', 't.id = cr.trunk_id')
            ->leftJoin('billing.service_trunk st', 'st.id = cr.trunk_service_id')
            ->leftJoin('stat.client_contract_type cct', 'cct.id = st.contract_type_id')
            ->leftJoin('nnp.operator o', 'o.id = cr.nnp_operator_id')
            ->leftJoin('nnp.country nc', 'nc.code = cr.nnp_country_code')
            ->leftJoin('nnp.region r', 'r.id = cr.nnp_region_id')
            ->leftJoin('nnp.city ci', 'ci.id = cr.nnp_city_id')
            ->leftJoin('billing.clients c', 'c.id = cr.account_id')
            ->leftJoin('billing.currency_rate rate', 'rate.currency::public.currencies = c.currency AND rate.date = now()::date')
            ->andWhere('cr.orig')
            ->orderBy('connect_time')
            ->limit(500);

        $query2->select(
            [
                'cr.cdr_id',
                'cr.billed_time session_time',
                't.name dst_route',
                'o.name dst_operator_name',
                'nc.name_rus dst_country_name',
                'r.name dst_region_name',
                'ci.name dst_city_name',
                'st.contract_number || \' (\' || cct.name || \')\' dst_contract_name',
                'cost_price' => new Expression(self::getMoneyCalculateExpression('cr.cost')),
                'term_rate' => new Expression(self::getMoneyCalculateExpression('cr.rate')),
            ]
        )
            ->from('calls_raw.calls_raw cr')
            ->leftJoin('auth.trunk t', 't.id = cr.trunk_id')
            ->leftJoin('billing.service_trunk st', 'st.id = cr.trunk_service_id')
            ->leftJoin('stat.client_contract_type cct', 'cct.id = st.contract_type_id')
            ->leftJoin('nnp.operator o', 'o.id = cr.nnp_operator_id')
            ->leftJoin('nnp.country nc', 'nc.code = cr.nnp_country_code')
            ->leftJoin('nnp.region r', 'r.id = cr.nnp_region_id')
            ->leftJoin('nnp.city ci', 'ci.id = cr.nnp_city_id')
            ->leftJoin('billing.clients c', 'c.id = cr.account_id')
            ->leftJoin('billing.currency_rate rate', 'rate.currency::public.currencies = c.currency AND rate.date = now()::date')
            ->andWhere('NOT cr.orig')
            ->orderBy('connect_time')
            ->limit(500);

        $null = new Expression('NULL');

        $query3->select(
            [
                'cdr_id' => 'id',
                'date_trunc(\'second\', setup_time) connect_time',
                'disconnect_cause',
                'src_route',
                'src_number',
                'dst_number',
                'pdd' => $null,
                'src_operator_name' => $null,
                'src_country_name' => $null,
                'src_region_name' => $null,
                'src_city_name' => $null,
                'src_contract_name' => $null,
                'sale' => $null,
                'orig_rate' => $null,
                'cdr_id1' => $null,
                'session_time' => $null,
                'dst_route',
                'dst_operator_name' => $null,
                'dst_country_name' => $null,
                'dst_region_name' => $null,
                'dst_city_name' => $null,
                'dst_contract_name' => $null,
                'cost_price' => $null,
                'term_rate' => $null,
                'margin' => $null,
            ]
        )->from('calls_cdr.cdr_unfinished')
            ->orderBy('setup_time')
            ->limit(500);

        $query4->select(
            [
                '*',
                '(@(cr1.sale)) - cr2.cost_price margin',
            ]
        )->from('cr1')
            ->join('JOIN', 'cr2', 'cr1.cdr_id = cr2.cdr_id');

        $this->server_ids
        && $query1->andWhere(['IN', 'cr.server_id', ':server_ids'], ['server_ids' => $this->server_ids])
        && $query2->andWhere(['IN', 'cr.server_id', ':server_ids'], ['server_ids' => $this->server_ids])
        && $query3
        && $query3->andWhere(['IN', 'server_id', ':server_ids'], ['server_ids' => $this->server_ids]);

        ($this->connect_time_from || $this->correct_connect_time_to)
        && $query1->andWhere(
            'cr.connect_time BETWEEN :connect_time_from AND :connect_time_to',
            [
                'connect_time_from' => $this->connect_time_from ? $this->connect_time_from : new Expression('to_timestamp(0)'),
                'connect_time_to' => $this->correct_connect_time_to ? $this->correct_connect_time_to : new Expression('now()')
            ]
        )
        && $query2->andWhere(
            'cr.connect_time BETWEEN :connect_time_from AND :connect_time_to',
            [
                'connect_time_from' => $this->connect_time_from ? $this->connect_time_from : new Expression('to_timestamp(0)'),
                'connect_time_to' => $this->correct_connect_time_to ? $this->correct_connect_time_to : new Expression('now()')
            ]
        )
        && $query3
        && $query3->andWhere(
            'setup_time BETWEEN :connect_time_from AND :connect_time_to',
            [
                'connect_time_from' => $this->connect_time_from ? $this->connect_time_from : new Expression('to_timestamp(0)'),
                'connect_time_to' => $this->correct_connect_time_to ? $this->correct_connect_time_to : new Expression('now()')
            ]
        );

        if ($this->session_time_from
            || $this->session_time_to
            || $this->dst_routes_ids
            || $this->dst_contracts_ids
            || $this->dst_operator_ids
            || $this->dst_regions_ids
            || $this->dst_cities_ids
            || $this->dst_countries_ids
            || $this->dst_destinations_ids
            || $this->dst_number
        ) {
            $query1->limit(-1)->orderBy([]);
            $query3 = null;
        }

        if ($this->src_routes_ids
            || $this->src_contracts_ids
            || $this->src_operator_ids
            || $this->src_regions_ids
            || $this->src_cities_ids
            || $this->src_countries_ids
            || $this->src_destinations_ids
            || $this->src_number
        ) {
            $query2->limit(-1)->orderBy([]);
            $query3 = null;
        }

        ($this->session_time_from || $this->session_time_to)
        && $query2->andWhere(
            'cr.billed_time BETWEEN :session_time_from AND :session_time_to',
            [
                'session_time_from' => $this->session_time_from ? (int) $this->session_time_from : 0,
                'session_time_to' => $this->session_time_to ? (int) $this->session_time_to : self::UNATTAINABLE_SESSION_TIME
            ]
        );

        if ($this->src_routes_ids || $this->src_contracts_ids || $this->dst_routes_ids || $this->dst_contracts_ids) {
            if (!is_array($src_filter = $this->src_routes_ids ? $this->src_routes_ids : $this->src_contracts_ids)) {
                $src_filter = [];
            }

            if (!is_array($dst_filter = $this->dst_routes_ids ? $this->dst_routes_ids : $this->dst_contracts_ids)) {
                $dst_filter = [];
            }

            if ($this->src_routes_ids || $this->src_contracts_ids) {
                $query1->andWhere(['IN', 'cr.trunk_service_id', ':src_routes_ids'], ['src_routes_ids' => $src_filter]);
                $query3 && $query3->leftJoin('auth.trunk t', 'cu.src_route = t.name')
                    ->leftJoin('billing.service_trunk st', 'st.trunk_id = ct.id')
                    ->andWhere('st.id = :src_routes_ids', ['src_routes_ids' => $src_filter]);
            }

            if ($this->dst_routes_ids || $this->dst_contracts_ids) {
                $query2->andWhere(['IN', 'cr.trunk_service_id', ':dst_routes_ids'], ['dst_routes_ids' => $dst_filter]);
                $query3 && $query3->leftJoin('auth.trunk t', 'cu.dst_route = t.name')
                    ->leftJoin('billing.service_trunk st', 'st.trunk_id = ct.id')
                    ->andWhere('st.id = :dst_routes_ids', ['dst_routes_ids' => $dst_filter]);
            }
        }

        $this->src_operator_ids
        && $query1->andWhere(
            ['IN', 'cr.nnp_operator_id', ':src_operator_ids'],
            ['src_operator_ids' => $this->src_operator_ids]
        );

        $this->src_regions_ids
        && $query1->andWhere(
            ['IN', 'cr.nnp_region_id', ':src_regions_ids'],
            ['src_regions_ids' => $this->src_regions_ids]
        );

        $this->src_cities_ids
        && $query1->andWhere(
            ['IN', 'cr.nnp_city_id', ':src_cities_ids'],
            ['src_cities_ids' => $this->src_cities_ids]
        );

        $this->src_countries_ids
        && $query1->andWhere(
            ['IN', 'cr.nnp_country_code', ':src_countries_ids'],
            ['src_countries_ids' => $this->src_countries_ids]
        );

        $this->src_destinations_ids
        && $query1
            ->leftJoin('nnp.number_range_destination nrd', 'cr.nnp_number_range_id = nrd.number_range_id')
            ->andWhere(
                ['IN', 'nrd.destination_id', ':src_destinations_ids'],
                ['src_destinations_ids' => $this->src_destinations_ids]
            );

        $this->dst_operator_ids
        && $query2->andWhere(
            ['IN', 'cr.nnp_operator_id', ':dst_operator_ids'],
            ['dst_operator_ids' => $this->dst_operator_ids]
        );

        $this->dst_regions_ids
        && $query2->andWhere(
            ['IN', 'cr.nnp_region_id', ':dst_regions_ids'],
            ['dst_regions_ids' => $this->dst_regions_ids]
        );

        $this->dst_cities_ids
        && $query2->andWhere(
            ['IN', 'cr.nnp_city_id', ':dst_cities_ids'],
            ['dst_cities_ids' => $this->dst_cities_ids]
        );

        $this->dst_countries_ids
        && $query2->andWhere(
            ['IN', 'cr.nnp_country_code', ':dst_countries_ids'],
            ['dst_countries_ids' => $this->dst_countries_ids]
        );

        $this->dst_destinations_ids
        && $query1
            ->leftJoin('nnp.number_range_destination nrd', 'cr.nnp_number_range_id = nrd.number_range_id')
            ->andWhere(
                ['IN', 'nrd.destination_id', ':dst_destinations_ids'],
                ['dst_destinations_ids' => $this->dst_destinations_ids]
            );

        $this->is_success_calls
        && $query1->andWhere(
            ['or', 'billed_time > 0', ['IN', 'disconnect_cause', ':success_cause']],
            ['success_cause' => DisconnectCause::$successCodes]
        )
        && $query2->andWhere(
            ['or', 'billed_time > 0', ['IN', 'disconnect_cause', ':success_cause']],
            ['success_cause' => DisconnectCause::$successCodes]
        )
        && $query3 = null;

        $this->dst_number
        && $query2->andWhere('cr.dst_number::varchar LIKE :dst_number || \'%\'', ['dst_number' => $this->dst_number])
        && $query3
        && $query3->andWhere('dst_number::varchar LIKE :dst_number || \'%\'', ['dst_number' => $this->dst_number]);

        $this->src_number
        && $query1->andWhere('cr.src_number LIKE :src_number || \'%\'', ['src_number' => $this->src_number])
        && $query3
        && $query3->andWhere('src_number::varchar LIKE :src_number || \'%\'', ['src_number' => $this->src_number]);

        $this->disconnect_causes
        && $query1->andWhere(
            ['IN', 'cr.disconnect_cause', ':disconnect_causes'],
            ['disconnect_causes' => $this->disconnect_causes]
        )
        && $query2->andWhere(
            ['IN', 'cr.disconnect_cause', ':disconnect_causes'],
            ['disconnect_causes' => $this->disconnect_causes]
        )
        && $query3
        && $query3->andWhere(
            ['IN', 'disconnect_cause', ':disconnect_causes'],
            ['disconnect_causes' => $this->disconnect_causes]
        );

        $query3 && $query4 = (new CTEQuery())->from(['cr' => $query4->union($query3)]);

        $query4->orderBy('connect_time');

        if ($this->group || $this->group_period || $this->aggr) {
            $fields = $groups = [];
            if ($this->group_period) {
                $query4->rightJoin(
                    'generate_series (:connect_time_from::timestamp ,:connect_time_to::timestamp,\'1' . $this->group_period . '\'::interval) gs',
                    'cr1.connect_time >= gs.gs AND cr1.connect_time <= gs.gs + interval \'1' . $this->group_period .'\''
                );
                $fields['interval'] = '"gs"."gs" || \' - \' || "gs"."gs" + interval \'1' . $this->group_period . '\'';
                $groups[] = '"gs"."gs"';
            }

            $fields = array_merge($fields, $this->group, array_intersect_key($this->aggrConst, array_flip($this->aggr)));
            $groups = array_merge($groups, $this->group);

            $sort = [];
            foreach ($fields as $key => $value) {
                if (!is_int($key)) {
                    $sort[] = $key;
                } else {
                    $sort[] = $value;
                }
            }

            $query1->limit(-1);
            $query2->limit(-1);
            $query3 && $query3->limit(-1);

            $query4->select($fields)
                ->groupBy($groups)
                ->orderBy($sort[0]);
        } else {
            $sort = [
                'connect_time',
                'session_time',
                'disconnect_cause',
                'src_number',
                'src_operator_name',
                'src_country_name',
                'src_region_name',
                'src_city_name',
                'dst_number',
                'dst_operator_name',
                'dst_country_name',
                'dst_region_name',
                'dst_city_name',
                'src_route',
                'src_contract_name',
                'dst_route',
                'dst_contract_name',
                'sale',
                'cost_price',
                'margin',
                'orig_rate',
                'term_rate',
                'pdd',
            ];
        }

        if ($this->sort) {
            $query1->orderBy([])->limit(-1);
            $query2->orderBy([])->limit(-1);
            $query3 && $query3->orderBy([])->limit(-1);
            $query4->orderBy([])->limit(-1);
        }

        $query4->addLinkQueries(['cr1' => $query1, 'cr2' => $query2]);

        return new ActiveDataProvider(
            [
                'db' => 'dbPgSlave',
                'query' => $query4,
                'pagination' => [],
                'totalCount' => $query4->rowCount(),
                'sort' => [
                    'attributes' => $sort,
                ],
            ]
        );
    }
}
