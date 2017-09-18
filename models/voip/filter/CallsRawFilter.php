<?php
/**
 * Calls_raw report model
 */

namespace app\models\voip\filter;

use app\classes\traits\GetListTrait;
use app\classes\yii\CTEQuery;
use app\helpers\DateTimeZoneHelper;
use app\models\billing\CallsRaw;
use app\models\Currency;
use app\models\CurrencyRate;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Connection;
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
 *
 * @property string $currency
 * @property float $currency_rate
 * @property float $sale
 * @property float $cost_price
 * @property float $margin
 * @property float $orig_rate
 * @property float $term_rate
 *
 * @property-read \DateTime $dateStart = null;
 * @property-read Connection $dbConn
 *
 * @property array src_trunk_group_ids
 * @property array dst_trunk_group_ids
 */
class CallsRawFilter extends CallsRaw
{
    use \app\classes\traits\CallsRawCacheReport;
    use \app\classes\traits\CallsRawSlowReport;

    const UNATTAINABLE_SESSION_TIME = 2592000;
    const EXACT_COUNT_LIMIT = 5000;

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

    public $src_trunk_group_ids = null;
    public $dst_trunk_group_ids = null;

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
                    'dst_number_type_ids',
                    'src_trunk_group_ids',
                    'dst_trunk_group_ids',
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
     * Имена полей отчета
     *
     * @return array
     */
    public function attributeLabels()
    {
        return parent::attributeLabels() + [
            //$groupConst
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
            'src_ndc_type_id' => 'Тип номера А',
            'dst_ndc_type_id' => 'Тип номера B',
            'sale' => 'Продажа',
            'cost_price' => 'Себестоимость',
            'orig_rate' => 'Тариф продажи',
            'term_rate' => 'Тариф себестоимости',

            // $aggrLabels
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

            // _indexFiler
            'src_physical_trunks_ids' => 'Физический транк-оригинатор',
            'src_trunk_group_ids' => 'Группа транка-оригинатора',
            'session_time' => 'Длительность разговора',
            'src_operator_ids' => 'Оператор номера А',
            'dst_operator_ids' => 'Оператор номера В',
            'disconnect_causes' => 'Код завершения',
            'src_logical_trunks_ids' => 'Логический транк-оригинатор',
            'dst_logical_trunks_ids' => 'Логический транк-терминатор',
            'src_countries_ids' => 'Страна номера А',
            'dst_countries_ids' => 'Страна номера B',
            'is_success_calls' => 'Только успешные попытки',
            'src_contracts_ids' => 'Договор номера А',
            'dst_contracts_ids' => 'Договор номера B',
            'src_regions_ids' => 'Регион номера А',
            'dst_regions_ids' => 'Регион номера B',
            'dst_trunk_group_ids' => 'Группа транка-терминатора',
            'src_cities_ids' => 'Город номера А',
            'dst_cities_ids' => 'Город номера B',
            'dst_physical_trunks_ids' => 'Физический транк-терминатор',
            'src_destinations_ids' => 'Направление номера А',
            'dst_destinations_ids' => 'Направление номера В',
            'src_number_type_ids' => 'Тип номера А',
            'dst_number_type_ids' => 'Тип номера В',
            'group_period' => 'Период группировки',
            'group' => 'Группировки',
            'server_ids' => 'Точка присоединения',
            'currency' => 'Валюта расчетов',

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
        $this->sort = Yii::$app->request->get('sort');

        parent::load($get);

        /**
         * BETWEEN делать сравнение <= со вторым параметром,
         * поэтому в интервал могут попасть лишние звонки.
         * Чтобы этого не случилось отнимает 1 секунду.
         * +
         * Проверяем не составляет ли интервал больше одного месяца
         */
        if ($this->connect_time_from) {
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

    public function getFilterGroups()
    {
        $groups = [];

        // простые именнованые значения
        foreach ([
                     'src_route',
                     'dst_route',
                     'src_number',
                     'dst_number',
                     'src_operator_name',
                     'dst_operator_name',
                     'src_country_name',
                     'dst_country_name',
                     'src_region_name',
                     'dst_region_name',
                     'src_city_name',
                     'dst_city_name',
                     'src_ndc_type_id',
                     'dst_ndc_type_id',
                     'sale',
                     'cost_price',
                     'orig_rate',
                     'term_rate'
                 ] as $field) {
            $groups[$field] = $this->getAttributeLabel($field);
        }

        return $groups;
    }

    public function getAggrGroups()
    {
        $fields = array_keys($this->aggrConst);

        $that = $this;

        $fields = array_map(function ($value) use ($that) {
            return $this->getAttributeLabel($value);
        }, array_combine($fields, $fields));

        return $fields;

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
     * Вернуть поле группировки и его алиас
     *
     * @param string $groupKey
     * @return array
     */
    public function getGroupKeyParts($groupKey)
    {
        $groupKeys = [
            'sale' => 'round(sale::numeric, 4)',
            'cost_price' => 'round(cost_price::numeric, 4)',
            'orig_rate' => 'round(orig_rate::numeric, 4)',
            'term_rate' => 'round(term_rate::numeric, 4)',
        ];

        return isset($groupKeys[$groupKey]) ?
            [$groupKey, $groupKeys[$groupKey]] :
            [$groupKey, $groupKey];
    }

    /**
     * Проверка на наличие обязательных фильтров
     *
     * @return bool
     */
    public function isFilteringPossible()
    {
        if ($this->connect_time_from) {
            $attributes = $this->getObjectNotEmptyValues(
                [
                    'aggrConst',
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
     * Проверка на противоречивость элементов, выбранных в фильтрах ННП
     *
     * @return bool
     */
    public function isNnpFiltersPossible()
    {
        $attributes = $this->getObjectNotEmptyValues(
            [
                'src_operator_ids',
                'dst_operator_ids',
                'src_regions_ids',
                'dst_regions_ids',
                'src_cities_ids',
                'dst_cities_ids',
                'src_countries_ids',
                'dst_countries_ids',
            ]
        );
        foreach ($attributes as $key => $value) {
            if ($value && is_array($value) && count(array_intersect($value, [GetListTrait::$isNull, GetListTrait::$isNotNull])) == 2) {
                return false;
            }
        }

        return true;
    }

    /**
     * Метод добавления фильтра по длительности звонка
     *
     * @param CTEQuery $query
     * @param $param
     * @return CTEQuery
     */
    private function setSessionCondition(CTEQuery $query, $param)
    {
        ($this->session_time_from || $this->session_time_to)
        && $query->andWhere(
            [
                'BETWEEN',
                $param,
                $this->session_time_from ? (int)$this->session_time_from : 0,
                $this->session_time_to ? (int)$this->session_time_to : self::UNATTAINABLE_SESSION_TIME
            ]
        );

        return $query;
    }

    /**
     * Добавление фильтрации по направлению и/или типу звонка
     *
     * @param CTEQuery $query
     * @param $destination
     * @param $number_type
     * @param $param
     * @return CTEQuery
     */
    private function setDestinationCondition(CTEQuery $query, $destination, $number_type, $param, $isGroup, $alias)
    {
        if ($destination || $number_type || $isGroup) {
            $query5 = new Query();
            $query5->select(
                [
                    "{$alias}_number_range_id" => 'number_range_id',
                    "{$alias}_ndc_type_id" => 'ndc_type_id',
                    "{$alias}_destination_id" => 'destination_id'
                ]
            )
                ->from("nnp.number_range_destination")
                ->andWhere("number_range_id = $param")
                ->limit(1);

            $destination
            && $query->andWhere(["{$alias}_nrd.{$alias}_destination_id" => $destination])
            && $query5->andWhere(["destination_id" => $destination]);

            $number_type
            && $query->andWhere(["{$alias}_nrd.{$alias}_ndc_type_id" => $number_type])
            && $query5->andWhere(["ndc_type_id" => $number_type]);

            $query->join('LEFT JOIN LATERAL', ["{$alias}_nrd" => $query5], "{$alias}_nrd.{$alias}_number_range_id = $param");
        }

        return $query;
    }

    /**
     * Отчет по calls_raw (живет по адресу /voip/raw)
     *
     * @return ActiveDataProvider|ArrayDataProvider
     */
    public function getReport()
    {
        if (!$this->isFilteringPossible() || !$this->isNnpFiltersPossible()) {
            return new ArrayDataProvider(
                [
                    'allModels' => [],
                ]
            );
        }

        $last_month = (new \DateTime())->setTimestamp(mktime(0, 0, 0, date('m') - 1, 1));

        $query = $this->dateStart >= $last_month ? $this->_getCacheReport() : $this->_getSlowReport();

        if ($this->group || $this->group_period || $this->aggr) {
            $fields = $groups = [];
            if ($this->group_period) {
                $query->rightJoin(
                    "generate_series ('{$this->connect_time_from}'::timestamp, " . ($this->correct_connect_time_to ? "'$this->correct_connect_time_to'" : 'now()') . "::timestamp, '1 {$this->group_period}'::interval) gs",
                    "connect_time >= gs.gs AND connect_time <= gs.gs + interval '1 {$this->group_period}'"
                );
                $fields['interval'] = "CAST(gs.gs AS varchar) || ' - ' || CAST(gs.gs AS timestamp) + interval '1 {$this->group_period}'";
                $groups[] = 'gs.gs';
            }

            $fields = array_merge($fields, $this->group, array_intersect_key($this->aggrConst, array_flip($this->aggr)));
            $groups = array_merge($groups, array_map(function ($value) {
                return $this->getGroupKeyParts($value)[0];
            }, $this->group));

            $sort = [];
            foreach ($fields as $key => $value) {
                if (!is_int($key)) {
                    $sort[] = $key;
                } else {
                    $sort[] = $this->getGroupKeyParts($value)[0];
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
             * Посему не следуют его использовать, если его неточность может повлить на вычисление количества страниц.
             */
            if ($count < self::EXACT_COUNT_LIMIT) {
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
                    'defaultOrder' => [(isset($sort[0]) ? $sort[0] : 'connect_time') => SORT_DESC],
                    'attributes' => $sort,
                ],
            ]
        );
    }
}
