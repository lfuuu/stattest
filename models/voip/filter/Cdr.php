<?php
/**
 * CDR report model
 */

namespace app\models\voip\filter;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use app\models\billing\DisconnectCause;
use app\classes\yii\CTEQuery;
use yii\db\Query;

/**
 * Class Cdr
 *
 * @property array $server_ids
 * @property string $connect_time_from
 * @property string $connect_time_to
 * @property string $session_time_from
 * @property string $session_time_to
 * @property bool $is_full_way
 * @property bool $is_success_calls
 * @property array $src_routes
 * @property array $dst_routes
 * @property string $src_number
 * @property string $dst_number
 * @property array $src_operator_ids
 * @property array $dst_operator_ids
 * @property array $src_region_ids
 * @property array $dst_region_ids
 * @property array $src_contracts
 * @property array $dst_contracts
 * @property string $releasing_party
 * @property int $call_id
 * @property array $disconnect_causes
 * @property string $redirect_number
 * @property array $src_country_prefixes
 * @property array $dst_country_prefixes
 * @property array $src_destination_ids
 * @property array $dst_destination_ids
 *
 * @property string $src_operator_name
 * @property string $dst_operator_name
 * @property string $src_region_name
 * @property string $dst_region_name
 *
 * @property array $group
 * @property array $aggr
 * @property string $group_period
 */
class Cdr extends Model
{
    const UNATTAINABLE_SESSION_TIME = 2592000;

    public $groupConst = [
        'src_route' => 'Транк-оригинатор',
        'dst_route' => 'Транк-терминатор',
        'src_number' => 'Номер А',
        'dst_number' => 'Номер В',
        'src_operator_name' => 'Оператор номера А',
        'dst_operator_name' => 'Оператор номера В',
        'src_region_name' => 'Регион номера А',
        'dst_region_name' => 'Регион номера В',
    ];

    public $groupFieldsConst = [
        'src_route' => 'cc.src_route',
        'dst_route' => 'cc.dst_route',
        'src_number' => 'cc.src_number',
        'dst_number' => 'cc.dst_number',
        'src_operator_name' => 'cr1.operator_name src_operator_name',
        'dst_operator_name' => 'cr2.operator_name dst_operator_name',
        'src_region_name' => 'cr1.region_name src_region_name',
        'dst_region_name' => 'cr2.region_name dst_region_name',
    ];

    public $aggrConst = [
        'sale_sum' => 'SUM(cr1.cost) ',
        'sale_avg' => 'round(AVG(cr1.cost)::numeric,2)',
        'sale_min' => 'MIN(cr1.cost) ',
        'sale_max' => 'MAX(cr1.cost)',
        'cost_price_sum' => 'SUM(cr2.cost)',
        'cost_price_avg' => 'round(AVG(cr2.cost)::numeric,2)',
        'cost_price_min' => 'MIN(cr2.cost)',
        'cost_price_max' => 'MAX(cr2.cost)',
        'margin_sum' => 'SUM((@(cr1.cost))-cr2.cost)',
        'margin_avg' => 'round(AVG((@(cr1.cost))-cr2.cost)::numeric,2)',
        'margin_min' => 'MIN((@(cr1.cost))-cr2.cost)',
        'margin_max' => 'MAX((@(cr1.cost))-cr2.cost)',
        'session_time_sum' => 'SUM(session_time)',
        'session_time_avg' => 'round(AVG(session_time)::numeric,2)',
        'session_time_min' => 'MIN(session_time)',
        'session_time_max' => 'MAX(session_time)',
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
    ];

    public $server_ids = null;
    public $connect_time_from = null;
    public $connect_time_to = null;
    public $session_time_from = null;
    public $session_time_to = null;
    public $is_full_way = null;
    public $is_success_calls = null;
    public $src_routes = null;
    public $dst_routes = null;
    public $src_number = null;
    public $dst_number = null;
    public $src_operator_ids = null;
    public $dst_operator_ids = null;
    public $src_region_ids = null;
    public $dst_region_ids = null;
    public $src_contracts = null;
    public $dst_contracts = null;

    public $releasing_party = null;
    public $call_id = null;
    public $disconnect_causes = null;
    public $redirect_number = null;
    public $src_country_prefixes = null;
    public $dst_country_prefixes = null;
    public $src_destination_ids = null;
    public $dst_destination_ids = null;

    public $src_operator_name = null;
    public $dst_operator_name = null;
    public $src_region_name = null;
    public $dst_region_name = null;

    public $group = [];

    public $aggr = [];

    public $group_period = '';

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
                    'session_time_from',
                    'session_time_to',
                    'is_full_way',
                    'is_success_calls',
                    'releasing_party',
                    'call_id',
                ],
                'integer'
            ],
            [
                [
                    'connect_time_from',
                    'connect_time_to',
                    'src_number',
                    'dst_number',
                    'redirect_number',
                    'group_period',
                ],
                'string'
            ],
            [
                [
                    'server_ids',
                    'src_operator_ids',
                    'dst_operator_ids',
                    'src_region_ids',
                    'dst_region_ids',
                    'disconnect_causes',
                    'src_country_prefixes',
                    'dst_country_prefixes',
                    'src_destination_ids',
                    'dst_destination_ids',
                ],
                'each',
                'rule' => ['integer']
            ],
            [
                [
                    'dst_routes',
                    'src_routes',
                    'src_contracts',
                    'dst_contracts',
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

        if (!is_array($this->group)) {
            $this->group = [];
        }

        if (!is_array($this->aggr)) {
            $this->aggr = [];
        }

        return $this->validate();
    }

    /**
     * Проверка на наличие обязательных фильтров
     *
     * @return bool
     */
    public function isFilteringPossible()
    {
        return $this->connect_time_from && $this->server_ids;
    }

    /**
     * Отчет по calls_cdr (живет по адресу /voip/cdr)
     *
     * @return ActiveDataProvider|ArrayDataProvider
     */
    public function getReport()
    {
        if (!$this->connect_time_from || !$this->server_ids) {
            return new ArrayDataProvider(
                [
                    'allModels' => [],
                ]
            );
        }

        $query1 = new Query();
        $query2 = new Query();
        $query3 = new CTEQuery();

        $this->server_ids
        && $query1->where(['IN', 'cc.server_id', ':server_ids'], ['server_ids' => $this->server_ids])
        && $query2->where(['IN', 'cr.server_id', ':server_ids'], ['server_ids' => $this->server_ids]);

        ($this->connect_time_from || $this->connect_time_to)
        && $query1->andWhere(
            'cc.connect_time BETWEEN :connect_time_from AND :connect_time_to',
            [
                'connect_time_from' => $this->connect_time_from ? $this->connect_time_from : new Expression('to_timestamp(0)'),
                'connect_time_to' => $this->connect_time_to ? $this->connect_time_to : new Expression('now()')
            ]
        )
        && $query2->andWhere(
            'cr.connect_time BETWEEN :connect_time_from AND :connect_time_to',
            [
                'connect_time_from' => $this->connect_time_from ? $this->connect_time_from : new Expression('to_timestamp(0)'),
                'connect_time_to' => $this->connect_time_to ? $this->connect_time_to : new Expression('now()')
            ]
        );

        ($this->session_time_from || $this->session_time_to)
        && $query1->andWhere(
            'cc.session_time BETWEEN :session_time_from AND :session_time_to',
            [
                'session_time_from' => $this->session_time_from ? (int) $this->session_time_from : 0,
                'session_time_to' => $this->session_time_to ? (int) $this->session_time_to : self::UNATTAINABLE_SESSION_TIME
            ]
        );

        if ($this->src_routes || $this->src_contracts) {
            $filter = &$this->src_routes ? $this->src_routes : $this->src_contracts;
            $query1->andWhere(['IN', 'cc.src_route', ':src_routes'], ['src_routes' => $filter]);
            $query2->andWhere(['IN', 't.name', ':src_routes'], ['src_routes' => $filter]);
        }

        if ($this->dst_routes || $this->dst_contracts) {
            $filter = &$this->dst_routes ? $this->dst_routes : $this->dst_contracts;
            $query1->andWhere(['IN', 'cc.dst_route', ':dst_routes'], ['dst_routes' => $filter]);
            $query2->andWhere(['IN', 't.name', ':dst_routes'], ['dst_routes' => $filter]);
        }

        $this->src_operator_ids
        && $query2->andWhere(
            ['or', 'NOT cr.orig', ['and', 'cr.orig', ['IN', 'cr.nnp_operator_id', ':src_operator_ids']]],
            ['src_operator_ids' => $this->src_operator_ids]
        )
        && $query3->andWhere(
            ['IN', 'cr1.nnp_operator_id', ':src_operator_ids'],
            ['src_operator_ids' => $this->src_operator_ids]
        );

        $this->src_region_ids
        && $query2->andWhere(
            ['or', 'NOT cr.orig', ['and', 'cr.orig', ['IN', 'cr.nnp_region_id', ':src_region_ids']]],
            ['src_region_ids' => $this->src_region_ids]
        )
        && $query3->andWhere(
            ['IN', 'cr1.nnp_region_id', ':src_region_ids'],
            ['src_region_ids' => $this->src_region_ids]
        );

        $this->src_destination_ids
        && $query2->andWhere(
            ['or', 'NOT cr.orig', ['and', 'cr.orig', ['IN', 'pd.destination_id', ':src_destination_ids']]],
            ['src_destination_ids' => $this->src_destination_ids]
        )
        && $query3->andWhere(
            ['IN', 'cr1.destination_id', ':src_destination_ids'],
            ['src_destination_ids' => $this->src_destination_ids]
        );

        $this->src_country_prefixes
        && $query2->andWhere(
            ['or', 'NOT cr.orig', ['and', 'cr.orig', ['IN', 'cr.nnp_country_prefix', ':src_country_prefixes']]],
            ['src_country_prefixes' => $this->src_country_prefixes]
        )
        && $query3->andWhere(
            ['IN', 'cr1.nnp_country_prefix', ':src_country_prefixes'],
            ['src_country_prefixes' => $this->src_country_prefixes]
        );

        $this->dst_operator_ids
        && $query2->andWhere(
            ['or', 'cr.orig', ['and', 'NOT cr.orig', ['IN', 'cr.nnp_operator_id', ':dst_operator_ids']]],
            ['dst_operator_ids' => $this->dst_operator_ids]
        )
        && $query3->andWhere(
            ['IN', 'cr2.nnp_operator_id', ':dst_operator_ids'],
            ['dst_operator_ids' => $this->dst_operator_ids]
        );

        $this->dst_region_ids
        && $query2->andWhere(
            ['or', 'cr.orig', ['and', 'NOT cr.orig', ['IN', 'cr.nnp_region_id', ':dst_region_ids']]],
            ['dst_region_ids' => $this->dst_region_ids]
        )
        && $query3->andWhere(
            ['IN', 'cr2.nnp_region_id', ':dst_region_ids'],
            ['dst_region_ids' => $this->dst_region_ids]
        );

        $this->dst_destination_ids
        && $query2->andWhere(
            ['or', 'cr.orig', ['and', 'NOT cr.orig', ['IN', 'pd.destination_id', ':dst_destination_ids']]],
            ['dst_destination_ids' => $this->dst_destination_ids]
        )
        && $query3->andWhere(
            ['IN', 'cr2.destination_id', ':dst_destination_ids'],
            ['dst_destination_ids' => $this->dst_destination_ids]
        );

        $this->dst_country_prefixes
        && $query2->andWhere(
            ['or', 'cr.orig', ['and', 'NOT cr.orig', ['IN', 'cr.nnp_country_prefix', ':dst_country_prefixes']]],
            ['dst_country_prefixes' => $this->dst_country_prefixes]
        )
        && $query3->andWhere(
            ['IN', 'cr1.nnp_country_prefix', ':dst_country_prefixes'],
            ['dst_country_prefixes' => $this->dst_country_prefixes]
        );

        $this->is_success_calls
        && $query1->andWhere(
            ['or', 'session_time > 0', ['IN', 'disconnect_cause', ':success_cause']],
            ['success_cause' => DisconnectCause::$successCodes]
        );

        $this->redirect_number
        && $query1->andWhere(
            'cc.redirect_number LIKE :redirect_number || \'%\'',
            ['redirect_number' => $this->redirect_number]
        );

        $this->dst_number
        && $query1->andWhere('cc.dst_number LIKE :dst_number || \'%\'', ['dst_number' => $this->dst_number])
        && $query2->andWhere('cr.dst_number::varchar LIKE :dst_number || \'%\'', ['dst_number' => $this->dst_number]);

        $this->src_number
        && $query1->andWhere('cc.src_number LIKE :src_number || \'%\'', ['src_number' => $this->src_number])
        && $query2->andWhere('cr.src_number::varchar LIKE :src_number || \'%\'', ['src_number' => $this->src_number]);

        $this->disconnect_causes
        && $query1->andWhere(
            ['IN', 'cc.disconnect_cause', ':disconnect_causes'],
            ['disconnect_causes' => $this->disconnect_causes]
        )
        && $query2->andWhere(
            ['IN', 'cr.disconnect_cause', ':disconnect_causes'],
            ['disconnect_causes' => $this->disconnect_causes]
        );

        $this->releasing_party
        && $query1->andWhere('cc.releasing_party = :releasing_party', ['releasing_party' => $this->releasing_party]);

        $this->call_id
        && $query1->andWhere('cc.call_id = :call_id', ['call_id' => $this->call_id]);

        $query1
            ->select(
                [
                    'cc.id',
                    'cc.call_id',
                    'date_trunc(\'second\', cc.setup_time) setup_time',
                    'cc.session_time',
                    'cc.disconnect_cause',
                    'cc.redirect_number',
                    'cc.releasing_party',
                    'cc.src_route',
                    'cc.dst_route',
                    'cc.src_number',
                    'cc.dst_number',
                    'date_trunc(\'second\', cc.connect_time) connect_time',
                    'date_trunc(\'second\', cc.connect_time - cc.setup_time) pdd'
                ]
            )
            ->from('calls_cdr.cdr cc');

        $query2->select(
            [
                'cr.cdr_id',
                'cr.orig',
                'o.name operator_name',
                'r.name region_name',
                'st.id contract',
                'st.contract_number',
                'cct.name contract_type',
                'cr.nnp_operator_id',
                'cr.nnp_region_id',
                'cr.nnp_country_prefix',
                'cost',
                'rate',
            ]
        )
            ->from('calls_raw.calls_raw cr')
            ->leftJoin('nnp.operator o', 'cr.nnp_operator_id = o.id')
            ->leftJoin('nnp.region r', 'r.id = cr.nnp_region_id')
            ->leftJoin('billing.service_trunk st', 'st.id = cr.trunk_id')
            ->leftJoin('stat.client_contract_type cct', 'cct.id = st.contract_type_id');

        if ($this->src_routes || $this->src_contracts || $this->dst_routes || $this->dst_contracts) {
            $query2->leftJoin('auth.trunk t', 'st.trunk_id = t.id');
        }

        if ($this->src_destination_ids || $this->dst_destination_ids) {
            $query2->leftJoin(
                'nnp.number_range nr',
                '(cr.orig 
                    AND 
                  cr.src_number >= nr.number_from 
                    AND 
                  cr.src_number <= nr.number_to) 
                    OR 
                 (NOT cr.orig
                    AND 
                  cr.dst_number >= nr.number_from 
                    AND 
                  cr.dst_number <= nr.number_to)'
            )
                ->leftJoin('nnp.number_range_prefix nrp', 'nr.id = nrp.number_range_id')
                ->leftJoin('nnp.prefix_destination pd', 'nrp.prefix_id = pd.prefix_id');

            $query2->addSelect('pd.destination_id destination_id');
        }

        $fields = [];
        if ($this->group || $this->group_period || $this->aggr) {
            foreach ($this->group as $value) {
                $fields[] = $this->groupFieldsConst[$value];
            }

            $fields = array_merge($fields, array_intersect_key($this->aggrConst, array_flip($this->aggr)));
            $groups = $this->group;
            if ($this->group_period) {
                $query3->rightJoin('generate_series (:connect_time_from::timestamp ,:connect_time_to::timestamp,\'1' .
                    $this->group_period . '\'::interval) gs',
                    'cc.connect_time >= gs.gs AND cc.connect_time <= gs.gs + interval \'1' . $this->group_period .'\'');
                $fields[] = '"gs"."gs" || \' - \' || "gs"."gs" + interval \'1' . $this->group_period . '\' interval';
                $groups[] = '"gs"."gs"';
            }

            $query3->select($fields)
                ->groupBy($groups);
        } else {
            $query3->select(
                [
                    'cc.*',
                    'cr1.operator_name src_operator_name',
                    'cr2.operator_name dst_operator_name',
                    'cr1.region_name src_region_name',
                    'cr2.region_name dst_region_name',
                    'cr1.cost sale',
                    'cr2.cost cost_price',
                    '(@(cr1.cost)) - cr2.cost margin',
                    'cr1.rate orig_rate',
                    'cr2.rate term_rate',
                    'cr1.contract_number || \' (\' || cr1.contract_type || \')\' src_contract_name',
                    'cr2.contract_number || \' (\' || cr2.contract_type || \')\' dst_contract_name',
                    'cr1.nnp_operator_id',
                    'cr2.nnp_operator_id',
                    'cr1.nnp_region_id',
                    'cr2.nnp_region_id'
                ]
            );
            $query1->limit(500);
        }

        $query3->from('cc')
            ->leftJoin('cr cr1', 'cc.id = cr1.cdr_id AND cr1.orig')
            ->leftJoin('cr cr2', 'cc.id = cr2.cdr_id AND NOT cr2.orig');

        $query3->addLinkQueries(['cc' => $query1, 'cr' => $query2]);

        return new ActiveDataProvider(
            [
                'db' => 'dbPgSlave',
                'query' => $query3,
                'pagination' => [],
                'totalCount' => 2000,
                'sort' => [
                    'attributes' => array_merge(
                        [
                            'call_id',
                            'setup_time',
                            'session_time',
                            'disconnect_cause',
                            'src_number',
                            'src_operator_name',
                            'src_region_name',
                            'dst_number',
                            'dst_operator_name',
                            'dst_region_name',
                            'redirect_number',
                            'src_route',
                            'src_contract_name',
                            'dst_route',
                            'dst_contract_name',
                            'sale',
                            'cost_price',
                            'margin',
                            'orig_rate',
                            'term_rate',
                            'releasing_party',
                            'connect_time',
                            'pdd',
                            'interval',
                        ],
                        array_keys($fields)
                    ),
                ],
            ]
        );
    }
}
