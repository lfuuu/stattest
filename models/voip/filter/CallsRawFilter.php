<?php
/**
 * Calls_raw report model
 */

namespace app\models\voip\filter;

use app\classes\yii\CTEQuery;
use app\helpers\DateTimeZoneHelper;
use app\models\billing\DisconnectCause;
use app\models\Currency;
use app\models\CurrencyRate;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
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
 * @property array $src_logical_trunks_ids
 * @property array $dst_logical_trunks_ids
 * @property array $src_physical_trunks_ids
 * @property array $dst_physical_trunks_ids
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
 * @property array $src_number_type_ids
 * @property array $dst_number_type_ids
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
        'sale_avg' => 'AVG(@(NULLIF(sale, 0)))',
        'sale_min' => 'MIN(@(sale))',
        'sale_max' => 'MAX(@(sale))',
        'cost_price_sum' => 'SUM(cost_price)',
        'cost_price_avg' => 'AVG(NULLIF(cost_price, 0))',
        'cost_price_min' => 'MIN(cost_price)',
        'cost_price_max' => 'MAX(cost_price)',
        'margin_sum' => 'SUM((@(sale)) - cost_price)',
        'margin_avg' => 'AVG(NULLIF((@(sale)) - cost_price, 0))',
        'margin_min' => 'MIN((@(sale)) - cost_price)',
        'margin_max' => 'MAX((@(sale)) - cost_price)',
        'session_time_sum' => 'SUM(session_time)',
        'session_time_avg' => 'AVG(session_time)',
        'session_time_min' => 'MIN(session_time)',
        'session_time_max' => 'MAX(session_time)',
        'calls_count' => 'COUNT(connect_time)',
        'nonzero_calls_count' => 'SUM((CASE WHEN session_time > 0 THEN 1 ELSE 0 END))',
        'acd' => 'SUM(session_time) / NULLIF(SUM((CASE WHEN session_time > 0 THEN 1 ELSE 0 END)), 0)',
        'asr' => 'SUM((CASE WHEN session_time > 0 THEN 1 ELSE 0 END))::real / NULLIF(COUNT(connect_time)::real, 0)',
        'acd_u' => 'SUM(session_time) / NULLIF(SUM((CASE WHEN disconnect_cause IN (16,17,18,19,21,31) THEN 1 ELSE 0 END)), 0)',
        'asr_u' => 'SUM((CASE WHEN disconnect_cause IN (16,17,18,19,21,31) THEN 1 ELSE 0 END))::real / NULLIF(COUNT(connect_time)::real, 0)',
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
        'acd' => 'ACD',
        'asr' => 'ASR',
        'acd_u' => 'ACD с кодами (16,17,18,19,21,31)',
        'asr_u' => 'ASR с кодами (16,17,18,19,21,31)',
    ];

    public $server_ids = [];
    public $connect_time_from = null;
    public $connect_time_to = null;
    public $correct_connect_time_to = null;
    public $session_time_from = null;
    public $session_time_to = null;
    public $is_success_calls = null;
    public $src_logical_trunks_ids = [];
    public $dst_logical_trunks_ids = [];
    public $src_physical_trunks_ids = [];
    public $dst_physical_trunks_ids = [];
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
    public $src_number_type_ids = [];
    public $dst_number_type_ids = [];

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

    public $currency = Currency::RUB;
    public $currency_rate = 1;

    public $sale = null;

    public $cost_price = null;

    public $margin = null;

    public $orig_rate = null;

    public $term_rate = null;

    public $dateStart = null;

    public $dbConn = null;

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
                    'dst_logical_trunks_ids',
                    'src_logical_trunks_ids',
                    'dst_physical_trunks_ids',
                    'src_physical_trunks_ids',
                    'src_contracts_ids',
                    'dst_contracts_ids',
                    'src_number_type_ids',
                    'dst_number_type_ids'
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
            $this->dateStart = new \DateTime($this->connect_time_from);
            $dateEnd = new \DateTime($this->connect_time_to);
            $dateEnd->modify('-1 second');
            $interval = $dateEnd->diff($this->dateStart);
            if ($interval->m > 1 || ($interval->m && ($interval->i || $interval->d || $interval->h || $interval->s))) {
                Yii::$app->session->addFlash('error', 'Временной период больше одного месяца');
                return false;
            }

            $this->correct_connect_time_to = $dateEnd->format(DateTimeZoneHelper::DATETIME_FORMAT);
        }

        if (isset($this->currency)) {
            $this->currency_rate = CurrencyRate::dao()->getRate($this->currency, date(DateTimeZoneHelper::DATE_FORMAT));
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
                    'correct_connect_time_to',
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
     * Формирование отчета путем обращения в кеширующей базе данных
     *
     * @return CTEQuery
     */
    public function getCacheReport()
    {
        $this->dbConn = Yii::$app->dbPgCache;

        $query = new CTEQuery();

        $query->select(
            [
                'connect_time',
                'disconnect_cause',
                'src_route',
                'src_number',
                'dst_number',
                'pdd',
                'src_operator_name',
                'src_country_name',
                'src_region_name',
                'src_city_name',
                'src_contract_name',
                'sale',
                'orig_rate',
                'session_time',
                'dst_route',
                'dst_operator_name',
                'dst_country_name',
                'dst_region_name',
                'dst_city_name',
                'dst_contract_name',
                'cost_price',
                'term_rate',
                'margin'
            ]
        )
        ->from('calls_raw_cache.calls_raw_cache');

        if ($this->connect_time_from || $this->correct_connect_time_to) {
            $query->andWhere(
                [
                    'BETWEEN',
                    'connect_time',
                    $this->connect_time_from,
                    $this->correct_connect_time_to ? $this->correct_connect_time_to : new Expression('now()'),
                ]
            );
        }

        ($this->session_time_from || $this->session_time_to)
        && $query->andWhere(
            [
                'BETWEEN',
                'session_time',
                $this->session_time_from ? (int)$this->session_time_from : 0,
                $this->session_time_to ? (int)$this->session_time_to : self::UNATTAINABLE_SESSION_TIME
            ]
        );

        $this->server_ids
        && $query->andWhere(['server_id' => $this->server_ids]);

        $this->src_physical_trunks_ids
        && $query->andWhere(['src_trunk_id' => $this->src_physical_trunks_ids]);

        $this->dst_physical_trunks_ids
        && $query->andWhere(['dst_trunk_id' => $this->dst_physical_trunks_ids]);

        $this->src_logical_trunks_ids
        && $query->andWhere(['src_trunk_service_id' => $this->src_logical_trunks_ids]);

        $this->dst_logical_trunks_ids
        && $query->andWhere(['dst_trunk_service_id' => $this->dst_logical_trunks_ids]);

        $this->src_contracts_ids
        && $query->andWhere(['src_contract_id' => $this->src_contracts_ids]);

        $this->dst_contracts_ids
        && $query->andWhere(['dst_contract_id' => $this->dst_contracts_ids]);

        $this->src_operator_ids
        && $query->andWhere(['src_nnp_operator_id' => $this->src_operator_ids]);

        $this->src_regions_ids
        && $query->andWhere(['src_nnp_region_id' => $this->src_regions_ids]);

        $this->src_cities_ids
        && $query->andWhere(['src_nnp_city_id' => $this->src_cities_ids]);

        $this->src_countries_ids
        && $query->andWhere(['src_nnp_country_code' => $this->src_countries_ids]);

        $this->dst_operator_ids
        && $query->andWhere(['dst_nnp_operator_id' => $this->dst_operator_ids]);

        $this->dst_regions_ids
        && $query->andWhere(['dst_nnp_region_id' => $this->dst_regions_ids]);

        $this->dst_cities_ids
        && $query->andWhere(['dst_nnp_city_id' => $this->dst_cities_ids]);

        $this->dst_countries_ids
        && $query->andWhere(['dst_nnp_country_code', $this->dst_countries_ids]);

        $this->is_success_calls
        && $query->andWhere(['or', 'session_time > 0', ['disconnect_cause' => DisconnectCause::$successCodes]]);

        if ($this->dst_number) {
            $this->dst_number = strtr($this->dst_number, ['.' => '_', '*' => '%']);
            $query->andWhere(['LIKE', 'dst_number', $this->dst_number, $isEscape = false]);
        }

        if ($this->src_number) {
            $this->src_number = strtr($this->src_number, ['.' => '_', '*' => '%']);
            $query->andWhere(['LIKE', 'src_number', $this->src_number, $isEscape = false]);
        }

        $this->disconnect_causes
        && $query->andWhere(['disconnect_cause' => $this->disconnect_causes]);

        if ($this->src_destinations_ids || $this->src_number_type_ids) {
            $condition = [];

            $this->src_destinations_ids
            && $query->andWhere(['src_nrd.destination_id' => $this->src_destinations_ids])
            && $condition[] = ['src_nrd.destination_id' => $this->src_destinations_ids];

            $this->src_number_type_ids
            && $query->andWhere(['src_nrd.ndc_type_id' => $this->src_number_type_ids])
            && $condition[] = ['src_nrd.ndc_type_id' => $this->src_number_type_ids];

            $condition = count($condition) == 2 ? ['AND', $condition[0], $condition[1]] : $condition[0];
            $query5 = new Query();
            $query5->from('nnp.number_range_destination src_nrd')
                ->andWhere(['AND', 'src_nnp_number_range_id = src_nrd.number_range_id', $condition])
                ->limit(1);
            $query->join('LEFT JOIN LATERAL', ['src_nrd' => $query5], 'src_nnp_number_range_id = src_nrd.number_range_id');
        }

        if ($this->dst_destinations_ids || $this->dst_number_type_ids) {
            $condition = [];

            $this->dst_destinations_ids
            && $query->andWhere(['dst_nrd.destination_id' => $this->dst_destinations_ids])
            && $condition[] = ['dst_nrd.destination_id' => $this->dst_destinations_ids];

            $this->dst_number_type_ids
            && $query->andWhere(['dst_nrd.ndc_type_id' => $this->dst_number_type_ids])
            && $condition[] = ['dst_nrd.ndc_type_id' => $this->dst_number_type_ids];

            $condition = count($condition) == 2 ? ['AND', $condition[0], $condition[1]] : $condition[0];
            $query5 = new Query();
            $query5->from('nnp.number_range_destination dst_nrd')
                ->andWhere(['AND', 'dst_nnp_number_range_id = dst_nrd.number_range_id', $condition])
                ->limit(1);
            $query->join('LEFT JOIN LATERAL', ['dst_nrd' => $query5], 'dst_nnp_number_range_id = dst_nrd.number_range_id');
        }

        return $query;
    }

    /**
     * Формирование отчета на основе "сырых" данных
     *
     * @return CTEQuery
     */
    public function getSlowReport()
    {
        $this->dbConn = Yii::$app->dbPgSlave;

        $query1 = new CTEQuery();
        $query2 = new CTEQuery();
        $query3 = new CTEQuery();
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
                    'cr.server_id'
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
                'cr.server_id'
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
                'cdr_id' => 'cu.id',
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
                'cu.server_id',
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
                'server_id1' => $null,
                'margin' => $null,
            ]
        )->from('calls_cdr.cdr_unfinished cu')
            ->orderBy('connect_time')
            ->limit(500);

        $query4->select(
            [
                '*',
                '(@(cr1.sale)) - cr2.cost_price margin',
            ]
        )->from('cr1')
            ->join('JOIN', 'cr2', ['AND', 'cr1.cdr_id = cr2.cdr_id', 'cr1.server_id = cr2.server_id']);

        if ($this->server_ids) {
            $condition = ['cr.server_id' => $this->server_ids];
            $query1->andWhere($condition)
            && $query2->andWhere($condition)
            && $query3
            && $query3->andWhere(['cu.server_id' => $this->server_ids]);
        }

        if ($this->connect_time_from || $this->correct_connect_time_to) {
            $condition = function ($field) {
                return [
                    'BETWEEN',
                    $field,
                    $this->connect_time_from ? $this->connect_time_from : new Expression('to_timestamp(0)'),
                    $this->correct_connect_time_to ? $this->correct_connect_time_to : new Expression('now()'),
                ];
            };
            $query1->andWhere($condition('cr.connect_time'));
            $query2->andWhere($condition('cr.connect_time'));
            $query3 && $query3->andWhere($condition('setup_time'));
        }

        if ($this->session_time_from
            || $this->session_time_to
            || $this->dst_logical_trunks_ids
            || $this->dst_contracts_ids
            || $this->dst_operator_ids
            || $this->dst_regions_ids
            || $this->dst_cities_ids
            || $this->dst_countries_ids
            || $this->dst_destinations_ids
            || $this->dst_number_type_ids
            || $this->dst_number
        ) {
            $query1->limit(-1)->orderBy([]);
            $query3 = null;
        }

        if ($this->src_logical_trunks_ids
            || $this->src_contracts_ids
            || $this->src_operator_ids
            || $this->src_regions_ids
            || $this->src_cities_ids
            || $this->src_countries_ids
            || $this->src_destinations_ids
            || $this->src_number_type_ids
            || $this->src_number
        ) {
            $query2->limit(-1)->orderBy([]);
            $query3 = null;
        }

        ($this->session_time_from || $this->session_time_to)
        && $query2->andWhere(
            [
                'BETWEEN',
                'cr.billed_time',
                $this->session_time_from ? (int)$this->session_time_from : 0,
                $this->session_time_to ? (int)$this->session_time_to : self::UNATTAINABLE_SESSION_TIME
            ]
        );

        if ($this->src_physical_trunks_ids) {
            $query1->andWhere(['cr.trunk_id' => $this->src_physical_trunks_ids])
            && $query3
            && $query3
                ->leftJoin('auth.trunk t1', 'src_route = t1.trunk_name')
                ->andWhere(['t1.id' => $this->src_physical_trunks_ids]);
        }

        if ($this->dst_physical_trunks_ids) {
            $query2->andWhere(['cr.trunk_id' => $this->dst_physical_trunks_ids])
            && $query3
            && $query3
                ->leftJoin('auth.trunk t2', 'dst_route = t2.trunk_name')
                ->andWhere(['t2.id' => $this->dst_physical_trunks_ids]);
        }

        $this->src_logical_trunks_ids
        && $query1->andWhere(['cr.trunk_service_id' => $this->src_logical_trunks_ids]);

        $this->dst_logical_trunks_ids
        && $query2->andWhere(['cr.trunk_service_id' => $this->dst_logical_trunks_ids]);

        $this->src_contracts_ids
        && $query1->andWhere(['st.contract_id' => $this->src_contracts_ids]);

        $this->dst_contracts_ids
        && $query2->andWhere(['st.contract_id' => $this->dst_contracts_ids]);

        $this->src_operator_ids
        && $query1->andWhere(['cr.nnp_operator_id' => $this->src_operator_ids]);

        $this->src_regions_ids
        && $query1->andWhere(['cr.nnp_region_id' => $this->src_regions_ids]);

        $this->src_cities_ids
        && $query1->andWhere(['cr.nnp_city_id' => $this->src_cities_ids]);

        $this->src_countries_ids
        && $query1->andWhere(['cr.nnp_country_code' => $this->src_countries_ids]);

        if ($this->src_destinations_ids || $this->src_number_type_ids) {
            $condition = [];

            $this->src_destinations_ids
            && $query1->andWhere(['nrd.destination_id' => $this->src_destinations_ids])
            && $condition[] = ['nrd.destination_id' => $this->src_destinations_ids];

            $this->src_number_type_ids
            && $query1->andWhere(['nrd.ndc_type_id' => $this->src_number_type_ids])
            && $condition[] = ['nrd.ndc_type_id' => $this->src_number_type_ids];

            $condition = count($condition) == 2 ? ['AND', $condition[0], $condition[1]] : $condition[0];
            $query5 = new Query();
            $query5->from('nnp.number_range_destination nrd')
                ->andWhere(['AND', 'cr.nnp_number_range_id = nrd.number_range_id', $condition])
                ->limit(1);
            $query1->join('LEFT JOIN LATERAL', ['nrd' => $query5], 'cr.nnp_number_range_id = nrd.number_range_id');
        }

        $this->dst_operator_ids
        && $query2->andWhere(['cr.nnp_operator_id' => $this->dst_operator_ids]);

        $this->dst_regions_ids
        && $query2->andWhere(['cr.nnp_region_id' => $this->dst_regions_ids]);

        $this->dst_cities_ids
        && $query2->andWhere(['cr.nnp_city_id' => $this->dst_cities_ids]);

        $this->dst_countries_ids
        && $query2->andWhere(['cr.nnp_country_code', $this->dst_countries_ids]);

        if ($this->dst_destinations_ids || $this->dst_number_type_ids) {
            $condition = [];

            $this->dst_destinations_ids
            && $query2->andWhere(['nrd.destination_id' => $this->dst_destinations_ids])
            && $condition[] = ['nrd.destination_id' => $this->dst_destinations_ids];

            $this->dst_number_type_ids
            && $query2->andWhere(['nrd.ndc_type_id' => $this->dst_number_type_ids])
            && $condition[] = ['nrd.ndc_type_id' => $this->dst_number_type_ids];

            $condition = count($condition) == 2 ? ['AND', $condition[0], $condition[1]] : $condition[0];
            $query5 = new Query();
            $query5->from('nnp.number_range_destination nrd')
                ->andWhere(['AND', 'cr.nnp_number_range_id = nrd.number_range_id', $condition])
                ->limit(1);
            $query2->join('LEFT JOIN LATERAL', ['nrd' => $query5], 'cr.nnp_number_range_id = nrd.number_range_id');
        }

        if ($this->is_success_calls) {
            $condition = ['or', 'billed_time > 0', ['disconnect_cause' => DisconnectCause::$successCodes]];
            $query1->andWhere($condition);
            $query2->andWhere($condition);
            $query3 = null;
        }

        /** @var Query $query3 */

        if ($this->dst_number) {
            $this->dst_number = strtr($this->dst_number, ['.' => '_', '*' => '%']);
            $query1->andWhere(['LIKE', 'CAST(cr.dst_number AS varchar)', $this->dst_number, $isEscape = false]);
            $query2->andWhere(['LIKE', 'CAST(cr.dst_number AS varchar)', $this->dst_number, $isEscape = false]);
            $query3 && $query3->andWhere(['LIKE', 'CAST(cu.dst_number AS varchar)', $this->dst_number, $isEscape = false]);
        }

        if ($this->src_number) {
            $this->src_number = strtr($this->src_number, ['.' => '_', '*' => '%']);
            $query1->andWhere(['LIKE', 'CAST(cr.src_number AS varchar)', $this->src_number, $isEscape = false]);
            $query2->andWhere(['LIKE', 'CAST(cr.src_number AS varchar)', $this->src_number, $isEscape = false]);
            $query3 && $query3->andWhere(['LIKE', 'CAST(cu.src_number AS varchar)', $this->src_number, $isEscape = false]);
        }

        if ($this->disconnect_causes) {
            $condition = ['cr.disconnect_cause' => $this->disconnect_causes];
            $query1->andWhere($condition)
            && $query2->andWhere($condition)
            && $query3
            && $query3->andWhere($condition);
        }

        $query3 && $query4 = (new CTEQuery())->from(['cr' => $query4->union($query3)]);

        if (($this->sort && $this->sort != 'connect_time') || $this->group || $this->group_period || $this->aggr) {
            $query1->orderBy([])->limit(-1);
            $query2->orderBy([])->limit(-1);
            $query3 && $query3->orderBy([])->limit(-1);
            $query4->orderBy([])->limit(-1);
        }

        $query4->addWith(['cr1' => $query1]);
        $query4->addWith(['cr2' => $query2]);

        return $query4;
    }

    /**
     * Отчет по calls_raw (живет по адресу /voip/raw)
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

        $last_month = (new \DateTime())->setTimestamp(mktime(0, 0, 0, date('m') - 1, 1));

        $query = $this->dateStart >= $last_month ? $this->getCacheReport() : $this->getSlowReport();

        $query->orderBy('connect_time');

        if ($this->group || $this->group_period || $this->aggr) {
            $fields = $groups = [];
            if ($this->group_period) {
                $query->rightJoin(
                    "generate_series ('{$this->connect_time_from}'::timestamp, '{$this->correct_connect_time_to}'::timestamp, '1 {$this->group_period}'::interval) gs",
                    "connect_time >= gs.gs AND connect_time <= gs.gs + interval '1 {$this->group_period}'"
                );
                $fields['interval'] = "CAST(gs.gs AS varchar) || ' - ' || CAST(gs.gs AS timestamp) + interval '1 {$this->group_period}'";
                $groups[] = 'gs.gs';
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

            $query->select($fields)
                ->groupBy($groups)
                ->orderBy($sort[0]);

            $count = $query->liteRowCount($this->dbConn);
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

            $count = $query->liteRowCount($this->dbConn);

            /**
             * Метод получения количества записей на основе статистики неточен.
             * Посему не следуют его использовать если его неточность может повлить на вычислени количества страниц.
             */
            if ($count < 5000) {
                $count = $query->rowCount($this->dbConn);
            }
        }

        return new ActiveDataProvider(
            [
                'db' => $this->dbConn,
                'query' => $query,
                'pagination' => [],
                'totalCount' => $count,
                'sort' => [
                    'attributes' => $sort,
                ],
            ]
        );
    }
}
