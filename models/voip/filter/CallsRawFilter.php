<?php
/**
 * Calls_raw report model
 */

namespace app\models\voip\filter;

use app\classes\helpers\DependecyHelper;
use app\classes\traits\GetListTrait;
use app\classes\WebApplication;
use app\classes\yii\CTEQuery;
use app\helpers\DateTimeZoneHelper;
use app\models\billing\CallsRaw;
use app\models\billing\CallsRawUnite;
use app\models\billing\Hub;
use app\models\Currency;
use app\models\CurrencyRate;
use Yii;
use yii\caching\TagDependency;
use yii\data\ArrayDataProvider;
use yii\db\Connection;
use yii\db\Expression;

/**
 * Class Raw
 *
 */
class CallsRawFilter extends CallsRaw
{
    use \app\classes\traits\CallsRawReport;
    use \app\classes\traits\CallsRawSlowReport;

    const UNATTAINABLE_SESSION_TIME = 2592000;
    const MAX_RAW_DATA_LIMIT = 100000;

    protected $requiredValues;

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
        'margin_percent' => '(SUM((@(sale)) - cost_price) / SUM(@(sale)) * 100)', // (margin_sum / (sale_sum / 100))
        'session_time_sum' => 'SUM(session_time)',
        'session_time_avg' => 'AVG(session_time)',
        'session_time_min' => 'MIN(session_time)',
        'session_time_max' => 'MAX(session_time)',
        'calls_count' => 'COUNT(cr1.connect_time)',
        'nonzero_calls_count' => 'SUM((CASE WHEN session_time > 0 THEN 1 ELSE 0 END))',
        'acd' => 'SUM(session_time) / NULLIF(SUM((CASE WHEN session_time > 0 THEN 1 ELSE 0 END)), 0)',
        'asr' => 'SUM((CASE WHEN session_time > 0 THEN 1 ELSE 0 END))::real / NULLIF(COUNT(cr1.connect_time)::real, 0)',
        'acd_u' => 'SUM(session_time) / NULLIF(SUM((CASE WHEN disconnect_cause IN (16,17,18,19,21,31) THEN 1 ELSE 0 END)), 0)',
        'asr_u' => 'SUM((CASE WHEN disconnect_cause IN (16,17,18,19,21,31) THEN 1 ELSE 0 END))::real / NULLIF(COUNT(cr1.connect_time)::real, 0)',
    ];
    public $currencyDependentFields = [
        'sale_sum',
        'sale_avg',
        'sale_min',
        'sale_max',
        'cost_price_sum',
        'cost_price_avg',
        'cost_price_min',
        'cost_price_max',
        'margin_sum',
        'margin_avg',
        'margin_min',
        'margin_max',
        'margin_percent',
    ];
    public $marketPlaceId = Hub::MARKET_PLACE_ID_RUSSIA;
    public $account_id = null;
    public $server_ids = [];
    public $trafficType = CallsRawUnite::TRAFFIC_TYPE_ALL;
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
    public $calls_with_duration = null;

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
    public $src_trunk_group_ids = null;
    public $dst_trunk_group_ids = null;
    public $is_exclude_internal_trunk_term = null;
    public $is_exclude_internal_trunk_orig = null;
    public $src_exclude_country = null;
    public $dst_exclude_country = null;
    
    /** @var Connection */
    public $dbConn = null;

    // Использовать версию расчёта, основанную на join'ах
    public $isNewVersion = false;
    // Использовать предрассчитанную таблицу
    public $isPreFetched = false;

    // Использовать старую склеку по peer_id
    public $isByPeerId = false;
    // Использовать таблицу склейки
    public $isFromUnite = false;

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
                    'marketPlaceId',
                    'account_id',
                    'trafficType',
                    'is_success_calls',
                    'session_time_from',
                    'session_time_to',
                    'calls_with_duration',
                    'is_exclude_internal_trunk_term',
                    'is_exclude_internal_trunk_orig',
                    'src_exclude_country',
                    'dst_exclude_country',
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
                'margin_percent' => 'Маржа: проценты',
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
                'session_time' => 'Длительность оригинации',
                'session_time_term' => 'Длительность терминации',
                'calls_with_duration' => 'Только звонки с длительностью',
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
                'marketPlaceId' => 'Биржа звонков',
                'trafficType' => 'Тип траффика',
                'server_ids' => 'Регион (точка подключения)',
                'currency' => 'Валюта расчетов',
                'is_exclude_internal_trunk_term' => 'Исключить внутренние транки Терминационные',
                'is_exclude_internal_trunk_orig' => 'Исключить внутренние транки Оригинационные',
                'src_exclude_country' => 'Кроме для стран номера A',
                'dst_exclude_country' => 'Кроме для стран номера B',
                'aggr' => 'Что считать',
            ];
    }

    /**
     * Custom data load
     *
     * @param array $get
     * @param string|null $sort
     * @return bool
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function loadCustom(array $get, $sort = null)
    {
        if ($sort) {
            $this->sort = $sort;
        } elseif (Yii::$app instanceof WebApplication) {
            $this->sort = Yii::$app->request->get('sort');
        }

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

            if ($dateEnd <= $this->dateStart) {
                $this->addError(
                    'connect_time_to',
                    'Неверный интервал.'
                );
            }

            $dateEnd->modify('-1 second');
            $interval = $dateEnd->diff($this->dateStart);
            if ($interval->m > 1 || ($interval->m && ($interval->i || $interval->d || $interval->h || $interval->s))) {
                $this->addError(
                    'connect_time_to',
                    'Временной период больше одного месяца'
                );
            }

            $this->correct_connect_time_to = $dateEnd->format(DateTimeZoneHelper::DATETIME_FORMAT);
        }

        $this->checkAttributes();

        if (!is_array($this->group)) {
            $this->group = [];
        }

        if (!is_array($this->aggr)) {
            $this->aggr = [];
        }

        return $this->validate(null, false);
    }

    /**
     * Проверка входных данных
     *
     * @throws \ReflectionException
     */
    protected function checkAttributes()
    {
        // check filter
        if ($this->hasRequiredFields()) {
            $this->addError('connect_time_from', 'Выберите время начала разговора и хотя бы еще одно поле');
        }
        // check multiply filters
        foreach ($this->getMultipleAttributesWithErrors() as $attribute) {
            $this->addError(
                $attribute,
                sprintf('Выбраны противоречивые значения в поле "%s"', $this->getAttributeLabel($attribute))
            );
        }
        // check currency rate
        if (isset($this->currency)) {
            try {
                $this->currency_rate = CurrencyRate::dao()
                    ->getRate(
                        $this->currency,
                        date(DateTimeZoneHelper::DATE_FORMAT)
                    );
            } catch (\Exception $e) {
                $this->addError('currency', $e->getMessage());
            }
        }
    }

    /**
     * Список группировок
     *
     * @return array
     */
    public function getFilterGroups()
    {
        $fields = [
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
        ];

        if ($exceptGroupFields = $this->getExceptGroupFields()) {
            $fields = array_diff($fields, $exceptGroupFields);
        }

        $groups = [];
        // именнованые значения
        foreach ($fields as $field) {
            $groups[$field] = $this->getAttributeLabel($field);
        }

        return $groups;
    }

    /**
     * Список исключенных группировок
     *
     * @return array
     */
    protected function getExceptGroupFields()
    {
        $exceptGroupFields = [];
        if ($this->isFromUnite) {
            $exceptGroupFields = [
//                'src_ndc_type_id',
//                'dst_ndc_type_id',
            ];
        }

        return $exceptGroupFields;
    }

    /**
     * Список исключенных аггрегаций
     *
     * @return array
     */
    protected function getExceptGroupAggregations()
    {
        $exceptAggregations = [];
        if ($this->isFromUnite) {
            $exceptAggregations = [
                'calls_count',
            ];
        }

        return $exceptAggregations;
    }

    /**
     * Список исключенных фильтров
     *
     * @return array
     */
    public function getExceptFilters()
    {
        $exceptFilters = ['trafficType'];
        if ($this->isFromUnite) {
            $exceptFilters = [
                'server_ids',

                'src_destinations_ids',
                'dst_destinations_ids',

                'is_success_calls',
            ];
        }

        return $exceptFilters;
    }

    /**
     * Список исключенных колонок
     *
     * @return array
     */
    public function getExceptColumns()
    {
        $exceptColumns = [];
        if ($this->isFromUnite) {
            $exceptColumns = [
                'pdd',
            ];
        }

        if ($this->isPreFetched) {
            $exceptColumns = [
                'src_number',
                'dst_number',
                'pdd',
            ];
        }

        return $exceptColumns;
    }

    /**
     * @return array
     */
    public function getAggrGroups()
    {
        // Если используется кэширование, то заменить при агрегации информацию по выборке
        if ($this->isRequestPreFetched()) {
            $this->aggrConst['calls_count'] = 'SUM(number_of_calls)';
            $this->aggrConst['asr'] = str_replace('COUNT(cr1.connect_time)', 'SUM(number_of_calls)', $this->aggrConst['asr']);
            $this->aggrConst['asr_u'] = str_replace('COUNT(cr1.connect_time)', 'SUM(number_of_calls)', $this->aggrConst['asr_u']);
        }

        $aggregations = array_keys($this->aggrConst);
        if ($exceptAggregations = $this->getExceptGroupAggregations()) {
            $aggregations = array_diff($aggregations, $exceptAggregations);
        }

        $aggregations = array_map(function ($value){
            return $this->getAttributeLabel($value);
        }, array_combine($aggregations, $aggregations));

        return $aggregations;

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
            'sale' => 'round(sale::numeric, 6)',
            'cost_price' => 'round(cost_price::numeric, 6)',
            'orig_rate' => 'round(orig_rate::numeric, 6)',
            'term_rate' => 'round(term_rate::numeric, 6)',
        ];

        return isset($groupKeys[$groupKey]) ?
            [$groupKey, $groupKeys[$groupKey]] :
            [$groupKey, $groupKey];
    }

    /**
     * Поля для заполнения
     *
     * @return array
     * @throws \ReflectionException
     */
    public function getRequiredValues()
    {
        if (is_null($this->requiredValues)) {
            $this->requiredValues = [];
            if ($this->connect_time_from) {
                $this->requiredValues = $this->getObjectNotEmptyValues(
                    [
                        'marketPlaceId',
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
                    ],
                    false
                );
            }
        }

        return $this->requiredValues;
    }

    /**
     * Есть ли поля для заполнения
     *
     * @return bool
     * @throws \ReflectionException
     */
    public function hasRequiredFields()
    {
        $required = $this->getRequiredValues();

        $exclude = [];
        if (
            $this->isFromUnite &&
            $required['trafficType'] === CallsRawUnite::TRAFFIC_TYPE_ALL
        ) {
            $exclude['trafficType'] = 1;
        }

        $required = array_diff_key(
            $required,
            $exclude
        );

        return !array_filter($required);
    }

    /**
     * Поля с противоречивостью элементов
     *
     * @return array
     */
    public function getMultipleAttributesWithErrors()
    {
        $all = $this->getAttributes(
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

        $attributes = [];
        foreach ($all as $key => $value) {
            if (
                $value &&
                    is_array($value) &&
                    count($value) > 1 &&
                    count(
                        array_intersect(
                            $value,
                            [GetListTrait::$isNull, GetListTrait::$isNotNull]
                        )
                    )
            ) {
                $attributes[] = $key;
            }
        }

        return $attributes;
    }

    /**
     * Получаем основной запрос
     *
     * @return CTEQuery
     * @throws \Exception
     */
    protected function getQuery()
    {
        if ($this->isByPeerId) {
            return $this->getReportNewByPeerId();
        }

        // by mcn_call_id
        if ($this->isFromUnite) {
            $this->isNewVersion = true;
            $this->isPreFetched = true;
            return $this->getReportFromUnite();
        }

        return $this->isNewVersion ?
            $this->getReportNew() : $this->getReportSlow();
    }

    /**
     * Возвращает список полей сортировки + устанавливает параметры группировки для запроса
     *
     * @param CTEQuery $query
     * @return array
     */
    protected function setQueryParamsAndGetSortFields(CTEQuery $query)
    {
        if ($this->group || $this->group_period || $this->aggr) {
            $fields = $groups = [];
            if ($this->group_period) {
                $query->rightJoin(
                    "generate_series ('{$this->connect_time_from}'::timestamp, " . ($this->correct_connect_time_to ? "'$this->correct_connect_time_to'" : 'now()') . "::timestamp, '1 {$this->group_period}'::interval) gs",
                    "cr1.connect_time >= gs.gs AND cr1.connect_time <= gs.gs + interval '1 {$this->group_period}'"
                );
                $fields['interval'] = "CAST(gs.gs AS varchar) || ' - ' || CAST(gs.gs AS timestamp) + interval '1 {$this->group_period}'";
                $groups[] = 'gs.gs';
            }

            $fields = array_merge($fields, $this->group, array_intersect_key($this->aggrConst, array_flip($this->aggr)));
            $groups = array_merge($groups, array_map(function ($value) {
                return $this->getGroupKeyParts($value)[0];
            }, $this->group));

            $sortFields = [];
            foreach ($fields as $key => $value) {
                $sortFields[] = !is_int($key) ?
                    $key : $this->getGroupKeyParts($value)[0];
            }

            $query->select($fields)
                ->groupBy($groups)
                ->orderBy($sortFields[0]);

        } else {
            $sortFields = [
                'connect_time',
                'session_time',
                'session_time_term',
                'disconnect_cause',
                'src_operator_name',
                'src_country_name',
                'src_region_name',
                'src_city_name',
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
            ];
            // Добавление полей, которые не поддерживает кеширование
            if (!$this->isNewVersion || !$this->isPreFetched) {
                $sortFields = array_merge($sortFields, [
                    'src_number',
                    'dst_number',
                    'pdd',
                ]);
            }
        }

        return $sortFields;
    }

    /**
     * Получаем результат
     *
     * @param CTEQuery $query
     * @return array|mixed
     * @throws \yii\db\Exception
     */
    protected function getResult(CTEQuery $query)
    {
        $dbConn = $this->dbConn;
        // Слейв справляется с такмим задачами лучше. Все отчеты на слейв.
        /*
        if ($this->isNewVersion && $this->isPreFetched) {
            $dbConn = Yii::$app->dbPg;
        }
        */


        if (!$this->group && !$this->aggr) {
            $count = $query->rowCount();
            if ($count > self::MAX_RAW_DATA_LIMIT) {
                $this->addError(
                    'id',
                    sprintf(
                        'Полный результат содержит более %s строк (%s). Скорректируйте запрос.',
                        number_format(self::MAX_RAW_DATA_LIMIT, 0, '.', ' '),
                        number_format($count, 0, '.', ' ')
                    )
                );

                return [];
            }

            if ($count == 0) {
                return [];
            }
        }
        //echo ($query->createCommand($dbConn)->rawSql);

        $queryCacheKey = CallsRaw::getCacheKey($query);
        if (!Yii::$app->cache->exists($queryCacheKey) || ($result = Yii::$app->cache->get($queryCacheKey)) === false) {
            $result = $query->createCommand($dbConn)->queryAll();
            if ($result) {
                Yii::$app->cache->set($queryCacheKey, $result, 0, (new TagDependency(['tags' => DependecyHelper::TAG_CALLS_RAW])));
            }
        }

        return $result;
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
     * Добавление фильтрации по направлению
     *
     * @param CTEQuery $query
     * @param $query3
     * @param array $destination
     * @param $alias
     * @return CTEQuery
     * @internal param mixed $param
     */
    private function setDestinationCondition(CTEQuery $query, $query3, $destination, $alias)
    {
        if (!$destination) {
            return $query;
        }

        $query->leftJoin(
            ["{$alias}_nrd" => 'nnp.number_range_destination'],
            "{$alias}_nrd.number_range_id = cr.nnp_number_range_id"
        );

        $query->andWhere(["{$alias}_nrd.destination_id" => $destination]);

        $query3 && $query3->addSelect(["{$alias}_destination_id" => new Expression('NULL')]);

        return $query;
    }

    /**
     * Отчет по calls_raw (/voip/old или /voip/raw/with-cache или /voip/raw/unite)
     *
     * @param bool $isGetDataProvider
     * @return array|mixed|ArrayDataProvider
     * @throws \ReflectionException
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function getReport($isGetDataProvider = true)
    {
        if ($this->hasErrors() || $this->hasRequiredFields()) {
            return $isGetDataProvider ?
                new ArrayDataProvider(['allModels' => [],]) : [];
        }

        // prepare query
        $query = $this->getQuery();
        // prepare grouping and sorting
        $sortFields = $this->setQueryParamsAndGetSortFields($query);
        // get result
        $result = $this->getResult($query);

        if (!$isGetDataProvider) {
            $this->_setCurrencyRate($result);
            return $result;
        }

        return new ArrayDataProvider(
            [
                'allModels' => $result,
                'pagination' => [],
                'totalCount' => count($result),
                'sort' => [
                    'defaultOrder' => [(isset($sortFields[0]) ? $sortFields[0] : 'connect_time') => SORT_DESC],
                    'attributes' => $sortFields,
                ],
            ]
        );
    }

    /**
     * Устанавливаем стоимость с учетом курса валют
     *
     * @param array $result
     */
    private function _setCurrencyRate(&$result)
    {
        foreach ($result as &$row) {
            foreach ($this->currencyDependentFields as $field) {
                if (isset($row[$field])) {
                    $row[$field] /= $this->currency_rate;
                }
            }
        }
    }

    /**
     * Является ли текущий запросом к предрассчитанной таблице
     *
     * @return bool
     */
    protected function isRequestPreFetched()
    {
        return
            $this->getIsNewRecord() &&
            $this->isPreFetched;
    }
}
