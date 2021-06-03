<?php

/**
 * Формирование отчета на основе "сырых" данных:
 *
 * - отчет перестроен, используя joins модель соединения
 * - подготовлен для подмены таблицы (calls_raw, calls_raw_cache)
 */

namespace app\classes\traits;

use app\classes\yii\CTEQuery;
use app\models\billing\CallsCdr;
use app\models\billing\CallsRaw;
use app\models\billing\CallsRawCache;
use app\models\billing\CallsRawUnite;
use app\models\billing\ClientContractType;
use app\models\billing\Clients;
use app\models\billing\CurrencyRate;
use app\models\billing\Hub;
use app\models\billing\Server;
use app\models\billing\ServiceTrunk;
use app\models\billing\Trunk;
use app\models\billing\TrunkGroupItem;
use app\models\billing\TrunkTrunkRule;
use app\models\Organization;
use app\modules\nnp\models\City;
use app\modules\nnp\models\Country;
use app\modules\nnp\models\NumberRange;
use app\modules\nnp\models\Operator;
use app\modules\nnp\models\Region;
use DateTime;
use Yii;
use yii\db\Expression;
use app\models\billing\DisconnectCause;
use yii\db\Query;

trait CallsRawReport
{

    /**
     * Расчёт отчёта, версия 2.1
     *
     * @return CTEQuery
     * @throws \Exception
     */
    protected function getReportNew()
    {
        $isPreFetched = $this->isPreFetched;
        $this->dbConn = Yii::$app->dbPgSlave;

        // Анонимная функция разрешения конфликта алиаса
        $aliasResolverFunc = function($alias) use ($isPreFetched) {
            if ($isPreFetched) {
                $alias = str_replace('.', '_', $alias);
            }
            return $alias;
        };

        $query = new CTEQuery;
        $select = [
            'connect_time' => $aliasResolverFunc('cr1.connect_time'),
            'session_time' => $aliasResolverFunc('cr1.billed_time'),
            'session_time_term' => $aliasResolverFunc('cr2.billed_time'),
            'disconnect_cause' => $aliasResolverFunc('cr1.disconnect_cause'),
            'src_route' => 't1.name',
            'dst_operator_name' => 'o1.name',
            'dst_country_name' => 'nc1.name_rus',
            'dst_region_name' => 'r1.name',
            'dst_city_name' => 'ci1.name',
            'st1.contract_number || \' (\' || cct1.name || \')\' src_contract_name',
            'sale' => new Expression("(
                CASE 
                   WHEN
                     c1.currency IS NOT NULL AND c1.currency != 'RUB'
                   THEN
                     @(" . $aliasResolverFunc('cr1.cost') . ") * rate1.rate
                   ELSE
                     @(" . $aliasResolverFunc('cr1.cost') . ")
                END
            )"),
            'orig_rate' => new Expression("(
                CASE 
                    WHEN
                      c1.currency IS NOT NULL AND c1.currency != 'RUB'
                    THEN
                      " . $aliasResolverFunc('cr1.rate') . " * rate1.rate
                    ELSE
                      " . $aliasResolverFunc('cr1.rate') . "
                END
            )"),
            'dst_route' => 't2.name',
            'src_operator_name' => 'o2.name',
            'src_country_name' => 'nc2.name_rus',
            'src_region_name' => 'r2.name',
            'src_city_name' => 'ci2.name',
            'st2.contract_number || \' (\' || cct2.name || \')\' dst_contract_name', //
            'cost_price' => new Expression("(
                CASE 
                   WHEN
                     c2.currency IS NOT NULL AND c2.currency != 'RUB'
                   THEN
                     " . $aliasResolverFunc('cr2.cost') . " * rate2.rate
                   ELSE
                     " . $aliasResolverFunc('cr2.cost') . "
                END
            )"),
            'term_rate' => new Expression("(
                CASE 
                   WHEN
                     c2.currency IS NOT NULL AND c2.currency != 'RUB'
                   THEN
                     " . $aliasResolverFunc('cr2.rate') . " * rate2.rate
                   ELSE
                     " . $aliasResolverFunc('cr2.rate') . "
                END
            )"),
        ];
        // Добавление в выборку отдельных колонок в зависимости от предрасчёта
        if ($isPreFetched) {
            $select = array_merge($select, [
                'number_of_calls' => 'cr1_number_of_calls'
            ]);
        } else {
            $select = array_merge($select, [
                'src_number' => new Expression('cr1.src_number::varchar'),
                'dst_number' => new Expression('cr1.dst_number::varchar'),
                'cr1.pdd',
            ]);
        }
        $query->select($select);
        // Определение основной таблицы
        if ($isPreFetched) {
            $query
                ->from(CallsRawCache::tableName())
                ->andWhere([
                    'market_place_id' => $this->marketPlaceId,
                ])
            ;
        } else {
            $query
                ->from(['cdr' => CallsCdr::tableName()])

                ->innerJoin(['s' => Server::tableName()], 's.id = cdr.server_id')
                ->innerJoin([
                    'h' => Hub::tableName()],
                    'h.id = s.hub_id' .
                        ' AND h.market_place_id = ' . $this->marketPlaceId
                )

                ->innerJoin(['cdr2' => CallsCdr::tableName()], 'cdr2.mcn_callid = cdr.mcn_callid')
                ->innerJoin(['s2' => Server::tableName()], 's2.id = cdr2.server_id')
                ->innerJoin([
                    'h2' => Hub::tableName()],
                    'h2.id = s2.hub_id' .
                        ' AND h2.market_place_id = ' . $this->marketPlaceId
                )

                //->innerJoin(['cr1' => CallsRaw::tableName()], 'cr1.mcn_callid = cdr.mcn_callid')
                ->innerJoin(['cr1' => CallsRaw::tableName()], 'cr1.cdr_id = cdr.id')

                //->innerJoin(['cr1' => CallsRaw::tableName()], 'cr1.mcn_callid = cdr.mcn_callid')
                ->innerJoin(['cr2' => CallsRaw::tableName()], 'cr2.cdr_id = cdr2.id')

                //->innerJoin(['cr2' => CallsRaw::tableName()], 'cr1.id = cr2.peer_id');

                ->andWhere(['IS NOT', 'cdr.mcn_callid', null])
                ->andWhere(['>', 'cdr.session_time', 0])

                ->andWhere(['IS NOT', 'cr1.account_id', null])
                ->andWhere(['IS NOT', 'cr2.account_id', null])
            ;
        }

        $query
            // Left joins к алиасу cr1 таблицы calls_raw.calls_raw
            ->leftJoin(['t1' => Trunk::tableName()], 't1.id = ' . $aliasResolverFunc('cr1.trunk_id'))
            ->leftJoin(['st1' => ServiceTrunk::tableName()], 'st1.id = ' . $aliasResolverFunc('cr1.trunk_service_id'))
            ->leftJoin(['cct1' => ClientContractType::tableName()], 'cct1.id = st1.contract_type_id')
            ->leftJoin(['o1' => Operator::tableName()], 'o1.id = ' . $aliasResolverFunc('cr1.nnp_operator_id'))
            ->leftJoin(['nc1' => Country::tableName()], 'nc1.code = ' . $aliasResolverFunc('cr1.nnp_country_code'))
            ->leftJoin(['r1' => Region::tableName()], 'r1.id = ' . $aliasResolverFunc('cr1.nnp_region_id'))
            ->leftJoin(['ci1' => City::tableName()], 'ci1.id = ' . $aliasResolverFunc('cr1.nnp_city_id'))
            ->leftJoin(['c1' => Clients::tableName()], 'c1.id = ' . $aliasResolverFunc('cr1.account_id'))
            ->leftJoin(['rate1' => CurrencyRate::tableName()], 'rate1.currency::public.currencies = c1.currency AND rate1.date = now()::date')
            // Left joins к алиасу cr2 таблицы calls_raw.calls_raw
            ->leftJoin(['t2' => Trunk::tableName()], 't2.id = ' . $aliasResolverFunc('cr2.trunk_id'))
            ->leftJoin(['st2' => ServiceTrunk::tableName()], 'st2.id = ' . $aliasResolverFunc('cr2.trunk_service_id'))
            ->leftJoin(['cct2' => ClientContractType::tableName()], 'cct2.id = st2.contract_type_id')
            ->leftJoin(['o2' => Operator::tableName()], 'o2.id = ' . $aliasResolverFunc('cr2.nnp_operator_id'))
            ->leftJoin(['nc2' => Country::tableName()], 'nc2.code = ' . $aliasResolverFunc('cr2.nnp_country_code'))
            ->leftJoin(['r2' => Region::tableName()], 'r2.id = ' . $aliasResolverFunc('cr2.nnp_region_id'))
            ->leftJoin(['ci2' => City::tableName()], 'ci2.id = ' . $aliasResolverFunc('cr2.nnp_city_id'))
            ->leftJoin(['c2' => Clients::tableName()], 'c2.id = ' . $aliasResolverFunc('cr2.account_id'))
            ->leftJoin(['rate2' => CurrencyRate::tableName()], 'rate2.currency::public.currencies = c2.currency AND rate2.date = now()::date');

        // Добавление условия для поля connect_time
        if ($this->connect_time_from || $this->correct_connect_time_to) {

            if ($isPreFetched) {
                $this->connect_time_from && $this->connect_time_from = (new DateTime($this->connect_time_from))
                    ->format('Y-m-d');
                $this->connect_time_to && $this->connect_time_to = (new DateTime($this->connect_time_to))
                    ->format('Y-m-d');

                $this->correct_connect_time_to && $this->correct_connect_time_to = (new DateTime($this->correct_connect_time_to))
                    ->format('Y-m-d');
            }

            $conditionFunc = function ($field) use ($isPreFetched) {
                return [
                    'BETWEEN',
                    $field,
                    $this->connect_time_from ? $this->connect_time_from : new Expression('to_timestamp(0)' . $isPreFetched ? ' :: date' : ''),
                    $this->correct_connect_time_to ? $this->correct_connect_time_to : new Expression('now()' . $isPreFetched ? ' :: date' : ''),
                ];
            };

            if (!$isPreFetched) {
                $query->andWhere(['AND',
                    $conditionFunc($aliasResolverFunc('cdr.connect_time')),
                ]);
                $query->andWhere(['AND',
                    $conditionFunc($aliasResolverFunc('cdr2.connect_time')),
                ]);
            }

            $query->andWhere(['AND',
                $conditionFunc($aliasResolverFunc('cr1.connect_time')),
            ]);
            if (!$isPreFetched) {
                $query->andWhere(['AND',
                    $conditionFunc($aliasResolverFunc('cr2.connect_time')),
                ]);
            }
        }

        if (!$isPreFetched) {
            $query->andWhere(['cr1.orig' => true, 'cr2.orig' => false,]);
        }

        // Добавление условия для поля server_id
        if ($this->server_ids) {
            $query->andWhere(['AND',
                [$aliasResolverFunc('cr1.server_id') => $this->server_ids],
                [$aliasResolverFunc('cr2.server_id') => $this->server_ids],
            ]);
        }
        // Добавление условия для поля t1.id
        if ($this->src_trunk_group_ids) {
            $query->andWhere([
                't1.id' => (new Query())
                    ->select('tgi1.trunk_id')
                    ->distinct()
                    ->from([
                        'tgi1' => TrunkGroupItem::tableName()
                    ])
                    ->andWhere(['tgi1.trunk_group_id' => $this->src_trunk_group_ids])
            ]);
        }
        // Добавление условия для поля t2.id
        if ($this->dst_trunk_group_ids) {
            $query->andWhere([
                't2.id' => (new Query())
                    ->select('tgi2.trunk_id')
                    ->distinct()
                    ->from([
                        'tgi2' => TrunkGroupItem::tableName(),
                        'ttr' => TrunkTrunkRule::tableName(),
                        'tgi3' => TrunkGroupItem::tableName(),
                    ])
                    ->andWhere([
                        'tgi.trunk_group_id' => $this->dst_trunk_group_ids,
                        'ttr.trunk_id' => 'tgi2.trunk_id',
                        'tgi3.trunk_group_id' => 'ttr.trunk_group_id',
                    ])
            ]);
        }
        // Left joins к алиасу cr1 таблицы calls_raw.calls_raw с дополнительным условием
        if ($this->is_exclude_internal_trunk_orig) {
            $query->leftJoin(['bst1' => ServiceTrunk::tableName()], $aliasResolverFunc('cr1.trunk_service_id') . ' = bst1.id');
            $query->leftJoin(['bc1' => Clients::tableName()], 'bc1.id = bst1.client_account_id AND bc1.organization_id = ' . Organization::INTERNAL_OFFICE);
            $query->andWhere(['bc1.id' => null]);
        }
        // Left joins к алиасу cr2 таблицы calls_raw.calls_raw с дополнительным условием
        if ($this->is_exclude_internal_trunk_term) {
            $query->leftJoin(['bst2' => ServiceTrunk::tableName()], $aliasResolverFunc('cr2.trunk_service_id') . ' = bst2.id');
            $query->leftJoin(['bc2' => Clients::tableName()], 'bc2.id = bst2.client_account_id AND bc2.organization_id = ' . Organization::INTERNAL_OFFICE);
            $query->andWhere(['bc2.id' => null]);
        }
        // Добавление условия для поля cr1.billed_time
        if (!$isPreFetched && ($this->session_time_from || $this->session_time_to)) {
            $query->andWhere(['BETWEEN',
                $aliasResolverFunc('cr1.billed_time'),
                $this->session_time_from ? (int)$this->session_time_from : 0,
                $this->session_time_to ? (int)$this->session_time_to : self::UNATTAINABLE_SESSION_TIME
            ]);
        }
        // Добавление только для предрассчитанных данных условия - Только звонки с длительностью
        if ($isPreFetched && $this->calls_with_duration) {
            $query->andWhere(['>', 'cr1_billed_time', 0]);
        }
        // Добавление условия для поля cr1.trunk_id
        if ($this->src_physical_trunks_ids) {
            $query->andWhere([$aliasResolverFunc('cr1.trunk_id') => $this->src_physical_trunks_ids]);
        }
        // Добавление условия для поля cr2.trunk_id
        if ($this->dst_physical_trunks_ids) {
            $query->andWhere([$aliasResolverFunc('cr2.trunk_id') => $this->dst_physical_trunks_ids]);
        }
        // Добавление условий для полей trunk_service_id, contract_id, nnp_operator_id, nnp_region_id, nnp_city_id, nnp_country_code основных таблиц cr1 и cr2
        $query = $query
            ->reportCondition($aliasResolverFunc('cr1.trunk_service_id'), $this->src_logical_trunks_ids)
            ->reportCondition($aliasResolverFunc('cr2.trunk_service_id'), $this->dst_logical_trunks_ids)

            ->reportCondition('st1.contract_id', $this->dst_contracts_ids)
            ->reportCondition('st2.contract_id', $this->src_contracts_ids)

            ->reportCondition($aliasResolverFunc('cr1.nnp_operator_id'), $this->dst_operator_ids)
            ->reportCondition($aliasResolverFunc('cr2.nnp_operator_id'), $this->src_operator_ids)

            ->reportCondition($aliasResolverFunc('cr1.nnp_region_id'), $this->dst_regions_ids)
            ->reportCondition($aliasResolverFunc('cr2.nnp_region_id'), $this->src_regions_ids)

            ->reportCondition($aliasResolverFunc('cr1.nnp_city_id'), $this->dst_cities_ids)
            ->reportCondition($aliasResolverFunc('cr2.nnp_city_id'), $this->src_cities_ids)

            ->reportCondition($aliasResolverFunc('cr1.nnp_country_code'), $this->dst_countries_ids)
            ->reportCondition($aliasResolverFunc('cr2.nnp_country_code'), $this->src_countries_ids);
        // Находится ли src_ndc_type_id в массиве $this->group
        $isSrcNdcTypeGroup = in_array('src_ndc_type_id', $this->group);
        // Left joins к алиасу cr2 таблицы calls_raw.calls_raw с дополнительным условием и выборкой
        if (!$isPreFetched && ($isSrcNdcTypeGroup || $this->src_destinations_ids || $this->src_number_type_ids)) {
            $query->leftJoin(
                ['src_nr' => NumberRange::tableName()],
                'src_nr.id = ' . $aliasResolverFunc('cr2.nnp_number_range_id')
            );
        }
        if ($isSrcNdcTypeGroup || $this->src_number_type_ids) {
            $query->addSelect(['src_ndc_type_id' => $aliasResolverFunc('src_nr.ndc_type_id')]);
        }
        if ($this->src_number_type_ids) {
            $query->andWhere([$aliasResolverFunc('src_nr.ndc_type_id') => $this->src_number_type_ids]);
        }
        // Находится ли dst_ndc_type_id в массиве $this->group
        $isDstNdcTypeGroup = in_array('dst_ndc_type_id', $this->group);
        // Left joins к алиасу cr1 таблицы calls_raw.calls_raw с дополнительным условием и выборкой
        if (!$isPreFetched && ($isDstNdcTypeGroup || $this->dst_destinations_ids || $this->dst_number_type_ids)) {
            $query->leftJoin(
                ['dst_nr' => NumberRange::tableName()],
                'dst_nr.id = ' . $aliasResolverFunc('cr1.nnp_number_range_id')
            );
        }
        if ($isDstNdcTypeGroup || $this->dst_number_type_ids) {
            $query->addSelect(['dst_ndc_type_id' => $aliasResolverFunc('dst_nr.ndc_type_id')]);
        }
        if ($this->dst_number_type_ids) {
            $query->andWhere([$aliasResolverFunc('dst_nr.ndc_type_id') => $this->dst_number_type_ids]);
        }
        // Left joins к алиасу cr1 таблицы calls_raw.calls_raw с дополнительным условием
        if (!$isPreFetched && $this->dst_destinations_ids) {
            $query->leftJoin(
                ['dst_nrd' => 'nnp.number_range_destination'],
                'dst_nrd.number_range_id = ' . $aliasResolverFunc('cr1.nnp_number_range_id')
            );
            $query->andWhere(['dst_nrd.destination_id' => $this->dst_destinations_ids]);
        }
        // Left joins к алиасу cr2 таблицы calls_raw.calls_raw с дополнительным условием
        if (!$isPreFetched && $this->src_destinations_ids) {
            $query->leftJoin(
                ['src_nrd' => 'nnp.number_range_destination'],
                'src_nrd.number_range_id = ' . $aliasResolverFunc('cr2.nnp_number_range_id')
            );
            $query->andWhere(['src_nrd.destination_id' => $this->src_destinations_ids]);
        }
        // Добавление условия для полей cr1.billed_time и cr2.billed_time
        if ($this->is_success_calls) {
            $query
                ->andWhere(['or',
                    $aliasResolverFunc('cr1.billed_time') . ' > 0',
                    [$isPreFetched ? 'cr1_disconnect_cause' : 'cr1.disconnect_cause' => DisconnectCause::$successCodes]
                ])
                ->andWhere(['or',
                    $aliasResolverFunc('cr2.billed_time') . ' > 0',
                    [$isPreFetched ? 'cr2_disconnect_cause' : 'cr2.disconnect_cause' => DisconnectCause::$successCodes]
                ]);
        }

        // Добавление условия для поля account_id
        if ($this->account_id) {
            $query
                ->andWhere(['or',
                    [$aliasResolverFunc('cr1.account_id') => $this->account_id],
                    [$aliasResolverFunc('cr2.account_id') => $this->account_id],
                ]);
        }

        // Если не требуется кеширование, то добавить условия для поля dst_number таблиц cr1 и cr2
        if (!$isPreFetched && $this->dst_number) {
            $this->dst_number = strtr($this->dst_number, ['.' => '_', '*' => '%']);
            $query
                ->andWhere(['LIKE', 'CAST(cr1.dst_number AS varchar)', $this->dst_number, $isEscape = false])
                ->andWhere(['LIKE', 'CAST(cr2.dst_number AS varchar)', $this->dst_number, $isEscape = false]);
        }
        // Если не требуется кеширование, то добавить условия для поля src_number таблиц cr1 и cr2
        if (!$isPreFetched && $this->src_number) {
            $this->src_number = strtr($this->src_number, ['.' => '_', '*' => '%']);
            $query
                ->andWhere(['LIKE', 'CAST(cr1.src_number AS varchar)', $this->src_number, $isEscape = false])
                ->andWhere(['LIKE', 'CAST(cr2.src_number AS varchar)', $this->src_number, $isEscape = false]);
        }
        // Добавление условия для поля disconnect_causes таблиц cr1 и cr2
        if ($this->disconnect_causes) {
            $conditionFunc = function ($condition) {
                return [$condition => $this->disconnect_causes];
            };
            $query
                ->andWhere($conditionFunc($aliasResolverFunc('cr1.disconnect_cause')))
                ->andWhere($conditionFunc($aliasResolverFunc('cr2.disconnect_cause')));
        }
        // Обертывание сформированного запроса в еще один запрос для рассчета поля margin
        $cteQuery = new CTEQuery;
        return $cteQuery
            ->select(['*', '(@(sale)) - cost_price margin'])
            ->from(['cr1' => $query]);
    }

    /**
     * Расчёт отчёта, по таблице склейки
     *
     * @return CTEQuery
     * @throws \Exception
     */
    protected function getReportFromUnite()
    {
        $this->dbConn = Yii::$app->dbPgSlave;

        // Анонимная функция разрешения конфликта алиаса
        $aliasResolverFunc = function($alias) {
            $alias = str_replace('.', '_', $alias);
            if (strpos($alias, 'cr1_') !== false) {
                $alias = str_replace('cr1_', '', $alias);
                $alias .= '_orig';
            }

            if (strpos($alias, 'cr2_') !== false) {
                $alias = str_replace('cr2_', '', $alias);
                $alias .= '_term';
            }

            return $alias;
        };

        $query = new CTEQuery;
        $select = [
            'connect_time' => $aliasResolverFunc('cr1.connect_time'),
            'session_time' => $aliasResolverFunc('cr1.billed_time'),
            'session_time_term' => $aliasResolverFunc('cr2.billed_time'),
            'disconnect_cause' => $aliasResolverFunc('cr1.disconnect_cause'),

            'src_route' => 't1.name',
            'dst_operator_name' => 'o1.name',
            'dst_country_name' => 'nc1.name_rus',
            'dst_region_name' => 'r1.name',
            'dst_city_name' => 'ci1.name',
            'st1.contract_number || \' (\' || cct1.name || \')\' src_contract_name',
            'sale' => new Expression("(
                CASE 
                   WHEN
                     c1.currency IS NOT NULL AND c1.currency != 'RUB'
                   THEN
                     @(" . $aliasResolverFunc('cr1.cost') . ") * rate1.rate
                   ELSE
                     @(" . $aliasResolverFunc('cr1.cost') . ")
                END
            )"),
            'orig_rate' => new Expression("(
                CASE 
                    WHEN
                      c1.currency IS NOT NULL AND c1.currency != 'RUB'
                    THEN
                      " . $aliasResolverFunc('cr1.rate') . " * rate1.rate
                    ELSE
                      " . $aliasResolverFunc('cr1.rate') . "
                END
            )"),

            'dst_route' => 't2.name',
            'src_operator_name' => 'o2.name',
            'src_country_name' => 'nc2.name_rus',
            'src_region_name' => 'r2.name',
            'src_city_name' => 'ci2.name',
            'st2.contract_number || \' (\' || cct2.name || \')\' dst_contract_name', //
            'cost_price' => new Expression("(
                CASE 
                   WHEN
                     c2.currency IS NOT NULL AND c2.currency != 'RUB'
                   THEN
                     " . $aliasResolverFunc('cr2.cost') . " * rate2.rate
                   ELSE
                     " . $aliasResolverFunc('cr2.cost') . "
                END
            )"),
            'term_rate' => new Expression("(
                CASE 
                   WHEN
                     c2.currency IS NOT NULL AND c2.currency != 'RUB'
                   THEN
                     " . $aliasResolverFunc('cr2.rate') . " * rate2.rate
                   ELSE
                     " . $aliasResolverFunc('cr2.rate') . "
                END
            )"),
        ];

        $select = array_merge($select, [
            'number_of_calls' => new Expression('1'),
            'src_number' => new Expression('src_number::varchar'),
            'dst_number' => new Expression('dst_number::varchar'),
        ]);

        $query->select($select);
        $query
            ->from(CallsRawUnite::tableName())
            ->andWhere([
                'market_place_id' => $this->marketPlaceId,
            ])
        ;

        $query
            // Left joins к алиасу cr1 таблицы calls_raw.calls_raw
            ->leftJoin(['t1' => Trunk::tableName()], 't1.id = ' . $aliasResolverFunc('cr1.trunk_id'))
            ->leftJoin(['st1' => ServiceTrunk::tableName()], 'st1.id = ' . $aliasResolverFunc('cr1.trunk_service_id'))
            ->leftJoin(['cct1' => ClientContractType::tableName()], 'cct1.id = st1.contract_type_id')

            ->leftJoin(['o1' => Operator::tableName()], 'o1.id = ' . $aliasResolverFunc('nnp_operator_id_b'))
            ->leftJoin(['nc1' => Country::tableName()], 'nc1.code = ' . $aliasResolverFunc('nnp_country_code_b'))
            ->leftJoin(['r1' => Region::tableName()], 'r1.id = ' . $aliasResolverFunc('nnp_region_id_b'))
            ->leftJoin(['ci1' => City::tableName()], 'ci1.id = ' . $aliasResolverFunc('nnp_city_id_b'))

            ->leftJoin(['c1' => Clients::tableName()], 'c1.id = ' . $aliasResolverFunc('cr1.account_id'))
            ->leftJoin(['rate1' => CurrencyRate::tableName()], 'rate1.currency::public.currencies = c1.currency AND rate1.date = now()::date')

            // Left joins к алиасу cr2 таблицы calls_raw.calls_raw
            ->leftJoin(['t2' => Trunk::tableName()], 't2.id = ' . $aliasResolverFunc('cr2.trunk_id'))
            ->leftJoin(['st2' => ServiceTrunk::tableName()], 'st2.id = ' . $aliasResolverFunc('cr2.trunk_service_id'))
            ->leftJoin(['cct2' => ClientContractType::tableName()], 'cct2.id = st2.contract_type_id')

            ->leftJoin(['o2' => Operator::tableName()], 'o2.id = ' . $aliasResolverFunc('nnp_operator_id_a'))
            ->leftJoin(['nc2' => Country::tableName()], 'nc2.code = ' . $aliasResolverFunc('nnp_country_code_a'))
            ->leftJoin(['r2' => Region::tableName()], 'r2.id = ' . $aliasResolverFunc('nnp_region_id_a'))
            ->leftJoin(['ci2' => City::tableName()], 'ci2.id = ' . $aliasResolverFunc('nnp_city_id_a'))

            ->leftJoin(['c2' => Clients::tableName()], 'c2.id = ' . $aliasResolverFunc('cr2.account_id'))
            ->leftJoin(['rate2' => CurrencyRate::tableName()], 'rate2.currency::public.currencies = c2.currency AND rate2.date = now()::date');

        // Добавление условия для поля connect_time
        if ($this->connect_time_from || $this->correct_connect_time_to) {
            $conditionFunc = function ($field) {
                return [
                    'BETWEEN',
                    $field,
                    $this->connect_time_from ? $this->connect_time_from : new Expression('to_timestamp(0)'),
                    $this->correct_connect_time_to ? $this->correct_connect_time_to : new Expression('now()'),
                ];
            };

            $query->andWhere(['AND',
                $conditionFunc($aliasResolverFunc('cr1.connect_time')),
            ]);
        }

        // Добавление условия для поля trafficType
        switch ($this->trafficType) {
            case CallsRawUnite::TRAFFIC_TYPE_CLIENT:
                $query->andWhere(['>=', 'type', CallsRawUnite::TYPE_RETAIL]);
                $query->andWhere(['!=', 'type', CallsRawUnite::TYPE_TRANSIT]);
                break;
            case CallsRawUnite::TRAFFIC_TYPE_RETAIL:
                $query->andWhere([
                    'type' => [
                        CallsRawUnite::TRAFFIC_TYPE_CLIENT_RETAIL,
                        CallsRawUnite::TRAFFIC_TYPE_CLIENT_AST,
                        CallsRawUnite::TYPE_MVNO,
                        CallsRawUnite::TYPE_MVNO_COST
                    ],
                ]);
                break;

            case CallsRawUnite::TRAFFIC_TYPE_OPERATOR:
            case CallsRawUnite::TRAFFIC_TYPE_CLIENT_RETAIL:
            case CallsRawUnite::TRAFFIC_TYPE_CLIENT_AST:
            case CallsRawUnite::TRAFFIC_TYPE_CLIENT_OTT:
                $query->andWhere([
                    'type' => $this->trafficType,
                ]);
                break;

            case CallsRawUnite::TRAFFIC_TYPE_CLIENT_MVNO:
                $query->andWhere([
                    'type' => [CallsRawUnite::TYPE_MVNO, CallsRawUnite::TYPE_MVNO_COST],
                ]);
                break;

            default:
                $query->andWhere(['>=', 'type', CallsRawUnite::TYPE_RETAIL]);

        }

        // Добавление условия для поля t1.id
        if ($this->src_trunk_group_ids) {
            $query->andWhere([
                't1.id' => (new Query())
                    ->select('tgi1.trunk_id')
                    ->distinct()
                    ->from([
                        'tgi1' => TrunkGroupItem::tableName()
                    ])
                    ->andWhere(['tgi1.trunk_group_id' => $this->src_trunk_group_ids])
            ]);
        }
        // Добавление условия для поля t2.id
        if ($this->dst_trunk_group_ids) {
            $query->andWhere([
                't2.id' => (new Query())
                    ->select('tgi2.trunk_id')
                    ->distinct()
                    ->from([
                        'tgi2' => TrunkGroupItem::tableName(),
                        'ttr' => TrunkTrunkRule::tableName(),
                        'tgi3' => TrunkGroupItem::tableName(),
                    ])
                    ->andWhere([
                        'tgi.trunk_group_id' => $this->dst_trunk_group_ids,
                        'ttr.trunk_id' => 'tgi2.trunk_id',
                        'tgi3.trunk_group_id' => 'ttr.trunk_group_id',
                    ])
            ]);
        }
        // Left joins к алиасу cr1 таблицы calls_raw.calls_raw с дополнительным условием
        if ($this->is_exclude_internal_trunk_orig) {
            $query->leftJoin(['bst1' => ServiceTrunk::tableName()], $aliasResolverFunc('cr1.trunk_service_id') . ' = bst1.id');
            $query->leftJoin(['bc1' => Clients::tableName()], 'bc1.id = bst1.client_account_id AND bc1.organization_id = ' . Organization::INTERNAL_OFFICE);
            $query->andWhere(['bc1.id' => null]);
        }
        // Left joins к алиасу cr2 таблицы calls_raw.calls_raw с дополнительным условием
        if ($this->is_exclude_internal_trunk_term) {
            $query->leftJoin(['bst2' => ServiceTrunk::tableName()], $aliasResolverFunc('cr2.trunk_service_id') . ' = bst2.id');
            $query->leftJoin(['bc2' => Clients::tableName()], 'bc2.id = bst2.client_account_id AND bc2.organization_id = ' . Organization::INTERNAL_OFFICE);
            $query->andWhere(['bc2.id' => null]);
        }
        // Добавление условия для поля cr1.billed_time
        if ($this->session_time_from || $this->session_time_to) {
            $query->andWhere(['BETWEEN',
                $aliasResolverFunc('cr1.billed_time'),
                $this->session_time_from ? (int)$this->session_time_from : 0,
                $this->session_time_to ? (int)$this->session_time_to : self::UNATTAINABLE_SESSION_TIME
            ]);
        }
        // Добавление только для предрассчитанных данных условия - Только звонки с длительностью
        if ($this->calls_with_duration) {
            $query->andWhere(['>', 'billed_time_orig', 0]);
        }
        // Добавление условия для поля cr1.trunk_id
        if ($this->src_physical_trunks_ids) {
            $query->andWhere([$aliasResolverFunc('cr1.trunk_id') => $this->src_physical_trunks_ids]);
        }
        // Добавление условия для поля cr2.trunk_id
        if ($this->dst_physical_trunks_ids) {
            $query->andWhere([$aliasResolverFunc('cr2.trunk_id') => $this->dst_physical_trunks_ids]);
        }
        // Добавление условий для полей trunk_service_id, contract_id,
        // nnp_operator_id, nnp_region_id, nnp_city_id, nnp_country_code основных таблиц cr1 и cr2
        $query = $query
            ->reportCondition($aliasResolverFunc('cr1.trunk_service_id'), $this->src_logical_trunks_ids)
            ->reportCondition($aliasResolverFunc('cr2.trunk_service_id'), $this->dst_logical_trunks_ids)

            ->reportCondition('st1.contract_id', $this->src_contracts_ids)
            ->reportCondition('st2.contract_id', $this->dst_contracts_ids)

            ->reportCondition($aliasResolverFunc('nnp_operator_id_a'), $this->src_operator_ids)
            ->reportCondition($aliasResolverFunc('nnp_operator_id_b'), $this->dst_operator_ids)

            ->reportCondition($aliasResolverFunc('nnp_region_id_a'), $this->src_regions_ids)
            ->reportCondition($aliasResolverFunc('nnp_region_id_b'), $this->dst_regions_ids)

            ->reportCondition($aliasResolverFunc('nnp_city_id_a'), $this->src_cities_ids)
            ->reportCondition($aliasResolverFunc('nnp_city_id_b'), $this->dst_cities_ids)
        ;
         
        $condition = [$aliasResolverFunc('nnp_country_code_a') => $this->src_countries_ids];
        if ($this->src_exclude_country) {
            $condition =  ['NOT', $condition];
        }
        $this->src_countries_ids && $query->andWhere($condition);

        $condition = [$aliasResolverFunc('nnp_country_code_b') => $this->dst_countries_ids];
        if ($this->dst_exclude_country) {
            $condition = ['NOT', $condition];
        } 
        $this->dst_countries_ids && $query->andWhere($condition);

        // Исключаем лишнее
//        if ($exceptGroupFields = $this->getExceptGroupFields()) {
//            $this->group = array_diff($this->group, $exceptGroupFields);
//        }

        // Находится ли src_ndc_type_id в массиве $this->group
        $isSrcNdcTypeGroup = in_array('src_ndc_type_id', $this->group);
        if ($isSrcNdcTypeGroup || $this->src_number_type_ids) {
            $query->addSelect(['src_ndc_type_id' => $aliasResolverFunc('ndc_type_id_a')]);
        }
        if ($this->src_number_type_ids) {
            $query->andWhere([$aliasResolverFunc('ndc_type_id_a') => $this->src_number_type_ids]);
        }

        // Находится ли dst_ndc_type_id в массиве $this->group
        $isDstNdcTypeGroup = in_array('dst_ndc_type_id', $this->group);

        if ($isDstNdcTypeGroup || $this->dst_number_type_ids) {
            $query->addSelect(['dst_ndc_type_id' => $aliasResolverFunc('ndc_type_id_b')]);
        }
        if ($this->dst_number_type_ids) {
            $query->andWhere([$aliasResolverFunc('ndc_type_id_b') => $this->dst_number_type_ids]);
        }

        // Если не требуется кеширование, то добавить условия для поля dst_number таблиц cr1 и cr2
        if ($this->dst_number) {
            $this->dst_number = strtr($this->dst_number, ['.' => '_', '*' => '%']);
            $query
                ->andWhere(['LIKE', 'CAST(dst_number AS varchar)', $this->dst_number, $isEscape = false])
                ->andWhere(['LIKE', 'CAST(dst_number AS varchar)', $this->dst_number, $isEscape = false]);
        }
        // Если не требуется кеширование, то добавить условия для поля src_number таблиц cr1 и cr2
        if ($this->src_number) {
            $this->src_number = strtr($this->src_number, ['.' => '_', '*' => '%']);
            $query
                ->andWhere(['LIKE', 'CAST(src_number AS varchar)', $this->src_number, $isEscape = false])
                ->andWhere(['LIKE', 'CAST(src_number AS varchar)', $this->src_number, $isEscape = false]);
        }

        // Добавление условия для поля account_id
        if ($this->account_id) {
            $query
                ->andWhere(['or',
                    [$aliasResolverFunc('cr1.account_id') => $this->account_id],
                    [$aliasResolverFunc('cr2.account_id') => $this->account_id],
                ]);
        }

        // Добавление условия для поля disconnect_causes таблиц cr1 и cr2
        if ($this->disconnect_causes) {
            $conditionFunc = function ($condition) {
                return [$condition => $this->disconnect_causes];
            };
            $query
                ->andWhere($conditionFunc($aliasResolverFunc('cr1.disconnect_cause')))
                ->andWhere($conditionFunc($aliasResolverFunc('cr2.disconnect_cause')));
        }
        // Обертывание сформированного запроса в еще один запрос для рассчета поля margin
        $cteQuery = new CTEQuery;
        return $cteQuery
            ->select(['*', '(@(sale)) - cost_price margin'])
            ->from(['cr1' => $query]);
    }

    /**
     * Старая склейка по peer_id
     *
     * @return CTEQuery
     * @throws \Exception
     */
    protected function getReportNewByPeerId()
    {
        $isPreFetched = $this->isPreFetched;
        $this->dbConn = Yii::$app->dbPgSlave;

        // Анонимная функция разрешения конфликта алиаса
        $aliasResolverFunc = function($alias) use ($isPreFetched) {
            if ($isPreFetched) {
                $alias = str_replace('.', '_', $alias);
            }
            return $alias;
        };

        $query = new CTEQuery;
        $select = [
            'connect_time' => $aliasResolverFunc('cr1.connect_time'),
            'session_time' => $aliasResolverFunc('cr1.billed_time'),
            'session_time_term' => $aliasResolverFunc('cr2.billed_time'),
            'disconnect_cause' => $aliasResolverFunc('cr1.disconnect_cause'),
            'src_route' => 't1.name',
            'dst_operator_name' => 'o1.name',
            'dst_country_name' => 'nc1.name_rus',
            'dst_region_name' => 'r1.name',
            'dst_city_name' => 'ci1.name',
            'st1.contract_number || \' (\' || cct1.name || \')\' src_contract_name',
            'sale' => new Expression("(
                CASE 
                   WHEN
                     c1.currency IS NOT NULL AND c1.currency != 'RUB'
                   THEN
                     @(" . $aliasResolverFunc('cr1.cost') . ") * rate1.rate
                   ELSE
                     @(" . $aliasResolverFunc('cr1.cost') . ")
                END
            )"),
            'orig_rate' => new Expression("(
                CASE 
                    WHEN
                      c1.currency IS NOT NULL AND c1.currency != 'RUB'
                    THEN
                      " . $aliasResolverFunc('cr1.rate') . " * rate1.rate
                    ELSE
                      " . $aliasResolverFunc('cr1.rate') . "
                END
            )"),
            'dst_route' => 't2.name',
            'src_operator_name' => 'o2.name',
            'src_country_name' => 'nc2.name_rus',
            'src_region_name' => 'r2.name',
            'src_city_name' => 'ci2.name',
            'st2.contract_number || \' (\' || cct2.name || \')\' dst_contract_name', //
            'cost_price' => new Expression("(
                CASE 
                   WHEN
                     c2.currency IS NOT NULL AND c2.currency != 'RUB'
                   THEN
                     " . $aliasResolverFunc('cr2.cost') . " * rate2.rate
                   ELSE
                     " . $aliasResolverFunc('cr2.cost') . "
                END
            )"),
            'term_rate' => new Expression("(
                CASE 
                   WHEN
                     c2.currency IS NOT NULL AND c2.currency != 'RUB'
                   THEN
                     " . $aliasResolverFunc('cr2.rate') . " * rate2.rate
                   ELSE
                     " . $aliasResolverFunc('cr2.rate') . "
                END
            )"),
        ];
        // Добавление в выборку отдельных колонок в зависимости от кэширования
        if ($isPreFetched) {
            $select = array_merge($select, [
                'number_of_calls' => 'cr1_number_of_calls'
            ]);
        } else {
            $select = array_merge($select, [
                'src_number' => new Expression('cr1.src_number::varchar'),
                'dst_number' => new Expression('cr1.dst_number::varchar'),
                'cr1.pdd',
            ]);
        }
        $query->select($select);
        // Определение основной таблицы
        if ($isPreFetched) {
            $query->from(CallsRawCache::tableName());
        } else {
            $callsRawTableName = CallsRaw::tableName();
            $query
                ->from(['cr1' => $callsRawTableName])
                ->innerJoin(['cr2' => $callsRawTableName], 'cr1.id = cr2.peer_id');
        }

        $query
            // Left joins к алиасу cr1 таблицы calls_raw.calls_raw
            ->leftJoin(['t1' => Trunk::tableName()], 't1.id = ' . $aliasResolverFunc('cr1.trunk_id'))
            ->leftJoin(['st1' => ServiceTrunk::tableName()], 'st1.id = ' . $aliasResolverFunc('cr1.trunk_service_id'))
            ->leftJoin(['cct1' => ClientContractType::tableName()], 'cct1.id = st1.contract_type_id')
            ->leftJoin(['o1' => Operator::tableName()], 'o1.id = ' . $aliasResolverFunc('cr1.nnp_operator_id'))
            ->leftJoin(['nc1' => Country::tableName()], 'nc1.code = ' . $aliasResolverFunc('cr1.nnp_country_code'))
            ->leftJoin(['r1' => Region::tableName()], 'r1.id = ' . $aliasResolverFunc('cr1.nnp_region_id'))
            ->leftJoin(['ci1' => City::tableName()], 'ci1.id = ' . $aliasResolverFunc('cr1.nnp_city_id'))
            ->leftJoin(['c1' => Clients::tableName()], 'c1.id = ' . $aliasResolverFunc('cr1.account_id'))
            ->leftJoin(['rate1' => CurrencyRate::tableName()], 'rate1.currency::public.currencies = c1.currency AND rate1.date = now()::date')
            // Left joins к алиасу cr2 таблицы calls_raw.calls_raw
            ->leftJoin(['t2' => Trunk::tableName()], 't2.id = ' . $aliasResolverFunc('cr2.trunk_id'))
            ->leftJoin(['st2' => ServiceTrunk::tableName()], 'st2.id = ' . $aliasResolverFunc('cr2.trunk_service_id'))
            ->leftJoin(['cct2' => ClientContractType::tableName()], 'cct2.id = st2.contract_type_id')
            ->leftJoin(['o2' => Operator::tableName()], 'o2.id = ' . $aliasResolverFunc('cr2.nnp_operator_id'))
            ->leftJoin(['nc2' => Country::tableName()], 'nc2.code = ' . $aliasResolverFunc('cr2.nnp_country_code'))
            ->leftJoin(['r2' => Region::tableName()], 'r2.id = ' . $aliasResolverFunc('cr2.nnp_region_id'))
            ->leftJoin(['ci2' => City::tableName()], 'ci2.id = ' . $aliasResolverFunc('cr2.nnp_city_id'))
            ->leftJoin(['c2' => Clients::tableName()], 'c2.id = ' . $aliasResolverFunc('cr2.account_id'))
            ->leftJoin(['rate2' => CurrencyRate::tableName()], 'rate2.currency::public.currencies = c2.currency AND rate2.date = now()::date');

        if (!$isPreFetched) {
            $query->andWhere(['cr1.orig' => true, 'cr2.orig' => false,]);
        }

        // Добавление условия для поля connect_time
        if ($this->connect_time_from || $this->correct_connect_time_to) {

            if ($isPreFetched) {
                $this->connect_time_from && $this->connect_time_from = (new DateTime($this->connect_time_from))
                    ->format('Y-m-d');
                $this->connect_time_to && $this->connect_time_to = (new DateTime($this->connect_time_to))
                    ->format('Y-m-d');

                $this->correct_connect_time_to && $this->correct_connect_time_to = (new DateTime($this->correct_connect_time_to))
                    ->format('Y-m-d');
            }

            $conditionFunc = function ($field) use ($isPreFetched) {
                return [
                    'BETWEEN',
                    $field,
                    $this->connect_time_from ? $this->connect_time_from : new Expression('to_timestamp(0)' . $isPreFetched ? ' :: date' : ''),
                    $this->correct_connect_time_to ? $this->correct_connect_time_to : new Expression('now()' . $isPreFetched ? ' :: date' : ''),
                ];
            };

            $query->andWhere(['AND',
                $conditionFunc($aliasResolverFunc('cr1.connect_time')),
            ]);

            $query->andWhere(['AND',
                $conditionFunc($aliasResolverFunc('cr2.connect_time')),
            ]);
        }
        // Добавление условия для поля server_id
        if ($this->server_ids) {
            $query->andWhere(['AND',
                [$aliasResolverFunc('cr1.server_id') => $this->server_ids],
                [$aliasResolverFunc('cr2.server_id') => $this->server_ids],
            ]);
        }
        // Добавление условия для поля account_id
        if ($this->account_id) {
            $query
                ->andWhere(['or',
                    [$aliasResolverFunc('cr1.account_id') => $this->account_id],
                    [$aliasResolverFunc('cr2.account_id') => $this->account_id],
                ]);
        }
        // Добавление условия для поля t1.id
        if ($this->src_trunk_group_ids) {
            $query->andWhere([
                't1.id' => (new Query())
                    ->select('tgi1.trunk_id')
                    ->distinct()
                    ->from([
                        'tgi1' => TrunkGroupItem::tableName()
                    ])
                    ->where(['tgi1.trunk_group_id' => $this->src_trunk_group_ids])
            ]);
        }
        // Добавление условия для поля t2.id
        if ($this->dst_trunk_group_ids) {
            $query->andWhere([
                't2.id' => (new Query())
                    ->select('tgi2.trunk_id')
                    ->distinct()
                    ->from([
                        'tgi2' => TrunkGroupItem::tableName(),
                        'ttr' => TrunkTrunkRule::tableName(),
                        'tgi3' => TrunkGroupItem::tableName(),
                    ])
                    ->where([
                        'tgi.trunk_group_id' => $this->dst_trunk_group_ids,
                        'ttr.trunk_id' => 'tgi2.trunk_id',
                        'tgi3.trunk_group_id' => 'ttr.trunk_group_id',
                    ])
            ]);
        }
        // Left joins к алиасу cr1 таблицы calls_raw.calls_raw с дополнительным условием
        if ($this->is_exclude_internal_trunk_orig) {
            $query->leftJoin(['bst1' => ServiceTrunk::tableName()], $aliasResolverFunc('cr1.trunk_service_id') . ' = bst1.id');
            $query->leftJoin(['bc1' => Clients::tableName()], 'bc1.id = bst1.client_account_id AND bc1.organization_id = ' . Organization::INTERNAL_OFFICE);
            $query->andWhere(['bc1.id' => null]);
        }
        // Left joins к алиасу cr2 таблицы calls_raw.calls_raw с дополнительным условием
        if ($this->is_exclude_internal_trunk_term) {
            $query->leftJoin(['bst2' => ServiceTrunk::tableName()], $aliasResolverFunc('cr2.trunk_service_id') . ' = bst2.id');
            $query->leftJoin(['bc2' => Clients::tableName()], 'bc2.id = bst2.client_account_id AND bc2.organization_id = ' . Organization::INTERNAL_OFFICE);
            $query->andWhere(['bc2.id' => null]);
        }
        // Добавление условия для поля cr1.billed_time
        if (!$isPreFetched && ($this->session_time_from || $this->session_time_to)) {
            $query->andWhere(['BETWEEN',
                $aliasResolverFunc('cr1.billed_time'),
                $this->session_time_from ? (int)$this->session_time_from : 0,
                $this->session_time_to ? (int)$this->session_time_to : self::UNATTAINABLE_SESSION_TIME
            ]);
        }
        // Добавление только для кэшированных данных условия - Только звонки с длительностью
        if ($isPreFetched && $this->calls_with_duration) {
            $query->andWhere(['>', 'cr1_billed_time', 0]);
        }
        // Добавление условия для поля cr1.trunk_id
        if ($this->src_physical_trunks_ids) {
            $query->andWhere([$aliasResolverFunc('cr1.trunk_id') => $this->src_physical_trunks_ids]);
        }
        // Добавление условия для поля cr2.trunk_id
        if ($this->dst_physical_trunks_ids) {
            $query->andWhere([$aliasResolverFunc('cr2.trunk_id') => $this->dst_physical_trunks_ids]);
        }
        // Добавление условий для полей trunk_service_id, contract_id, nnp_operator_id, nnp_region_id, nnp_city_id, nnp_country_code основных таблиц cr1 и cr2
        $query = $query
            ->reportCondition($aliasResolverFunc('cr1.trunk_service_id'), $this->src_logical_trunks_ids)
            ->reportCondition($aliasResolverFunc('cr2.trunk_service_id'), $this->dst_logical_trunks_ids)

            ->reportCondition('st1.contract_id', $this->dst_contracts_ids)
            ->reportCondition('st2.contract_id', $this->src_contracts_ids)

            ->reportCondition($aliasResolverFunc('cr1.nnp_operator_id'), $this->dst_operator_ids)
            ->reportCondition($aliasResolverFunc('cr2.nnp_operator_id'), $this->src_operator_ids)

            ->reportCondition($aliasResolverFunc('cr1.nnp_region_id'), $this->dst_regions_ids)
            ->reportCondition($aliasResolverFunc('cr2.nnp_region_id'), $this->src_regions_ids)

            ->reportCondition($aliasResolverFunc('cr1.nnp_city_id'), $this->dst_cities_ids)
            ->reportCondition($aliasResolverFunc('cr2.nnp_city_id'), $this->src_cities_ids)

            ->reportCondition($aliasResolverFunc('cr1.nnp_country_code'), $this->dst_countries_ids)
            ->reportCondition($aliasResolverFunc('cr2.nnp_country_code'), $this->src_countries_ids);
        // Находится ли src_ndc_type_id в массиве $this->group
        $isSrcNdcTypeGroup = in_array('src_ndc_type_id', $this->group);
        // Left joins к алиасу cr2 таблицы calls_raw.calls_raw с дополнительным условием и выборкой
        if (!$isPreFetched && ($isSrcNdcTypeGroup || $this->src_destinations_ids || $this->src_number_type_ids)) {
            $query->leftJoin(
                ['src_nr' => NumberRange::tableName()],
                'src_nr.id = ' . $aliasResolverFunc('cr2.nnp_number_range_id')
            );
        }
        if ($isSrcNdcTypeGroup || $this->src_number_type_ids) {
            $query->addSelect(['src_ndc_type_id' => $aliasResolverFunc('src_nr.ndc_type_id')]);
        }
        if ($this->src_number_type_ids) {
            $query->andWhere([$aliasResolverFunc('src_nr.ndc_type_id') => $this->src_number_type_ids]);
        }
        // Находится ли dst_ndc_type_id в массиве $this->group
        $isDstNdcTypeGroup = in_array('dst_ndc_type_id', $this->group);
        // Left joins к алиасу cr1 таблицы calls_raw.calls_raw с дополнительным условием и выборкой
        if (!$isPreFetched && ($isDstNdcTypeGroup || $this->dst_destinations_ids || $this->dst_number_type_ids)) {
            $query->leftJoin(
                ['dst_nr' => NumberRange::tableName()],
                'dst_nr.id = ' . $aliasResolverFunc('cr1.nnp_number_range_id')
            );
        }
        if ($isDstNdcTypeGroup || $this->dst_number_type_ids) {
            $query->addSelect(['dst_ndc_type_id' => $aliasResolverFunc('dst_nr.ndc_type_id')]);
        }
        if ($this->dst_number_type_ids) {
            $query->andWhere([$aliasResolverFunc('dst_nr.ndc_type_id') => $this->dst_number_type_ids]);
        }
        // Left joins к алиасу cr1 таблицы calls_raw.calls_raw с дополнительным условием
        if (!$isPreFetched && $this->dst_destinations_ids) {
            $query->leftJoin(
                ['dst_nrd' => 'nnp.number_range_destination'],
                'dst_nrd.number_range_id = ' . $aliasResolverFunc('cr1.nnp_number_range_id')
            );
            $query->andWhere(['dst_nrd.destination_id' => $this->dst_destinations_ids]);
        }
        // Left joins к алиасу cr2 таблицы calls_raw.calls_raw с дополнительным условием
        if (!$isPreFetched && $this->src_destinations_ids) {
            $query->leftJoin(
                ['src_nrd' => 'nnp.number_range_destination'],
                'src_nrd.number_range_id = ' . $aliasResolverFunc('cr2.nnp_number_range_id')
            );
            $query->andWhere(['src_nrd.destination_id' => $this->src_destinations_ids]);
        }
        // Добавление условия для полей cr1.billed_time и cr2.billed_time
        if ($this->is_success_calls) {
            $query
                ->andWhere(['or',
                    $aliasResolverFunc('cr1.billed_time') . ' > 0',
                    [$isPreFetched ? 'cr1_disconnect_cause' : 'disconnect_cause' => DisconnectCause::$successCodes]
                ])
                ->andWhere(['or',
                    $aliasResolverFunc('cr2.billed_time') . ' > 0',
                    [$isPreFetched ? 'cr2_disconnect_cause' : 'disconnect_cause' => DisconnectCause::$successCodes]
                ]);
        }
        // Если не требуется кеширование, то добавить условия для поля dst_number таблиц cr1 и cr2
        if (!$isPreFetched && $this->dst_number) {
            $this->dst_number = strtr($this->dst_number, ['.' => '_', '*' => '%']);
            $query
                ->andWhere(['LIKE', 'CAST(cr1.dst_number AS varchar)', $this->dst_number, $isEscape = false])
                ->andWhere(['LIKE', 'CAST(cr2.dst_number AS varchar)', $this->dst_number, $isEscape = false]);
        }
        // Если не требуется кеширование, то добавить условия для поля src_number таблиц cr1 и cr2
        if (!$isPreFetched && $this->src_number) {
            $this->src_number = strtr($this->src_number, ['.' => '_', '*' => '%']);
            $query
                ->andWhere(['LIKE', 'CAST(cr1.src_number AS varchar)', $this->src_number, $isEscape = false])
                ->andWhere(['LIKE', 'CAST(cr2.src_number AS varchar)', $this->src_number, $isEscape = false]);
        }
        // Добавление условия для поля disconnect_causes таблиц cr1 и cr2
        if ($this->disconnect_causes) {
            $conditionFunc = function ($condition) {
                return [$condition => $this->disconnect_causes];
            };
            $query
                ->andWhere($conditionFunc($aliasResolverFunc('cr1.disconnect_cause')))
                ->andWhere($conditionFunc($aliasResolverFunc('cr2.disconnect_cause')));
        }
        // Обертывание сформированного запроса в еще один запрос для рассчета поля margin
        $cteQuery = new CTEQuery;
        return $cteQuery
            ->select(['*', '(@(sale)) - cost_price margin'])
            ->from(['cr1' => $query]);
    }
}
