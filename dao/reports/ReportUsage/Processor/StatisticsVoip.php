<?php

namespace app\dao\reports\ReportUsage\Processor;

use app\dao\reports\ReportUsage\Config;
use app\dao\reports\ReportUsage\Helper;
use app\dao\reports\ReportUsage\Processor;
use app\models\billing\Pricelist;
use app\models\Currency;
use app\modules\nnp\models\NdcType;
use app\modules\nnp\models\NumberRange;
use DateTime;
use yii\db\ActiveQuery;
use yii\db\Expression;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\billing\CallsRaw;
use app\modules\nnp\models\PackageMinute;
use app\modules\nnp\models\PackagePrice;
use app\modules\nnp\models\PackagePricelist;
use yii\db\Query;
use app\modules\nnp\models\City;
use app\modules\nnp\models\Country;

class StatisticsVoip extends Processor
{
    protected $totals;
    protected $decimals = 2;

    protected $fetched = [];

    protected $isNeedFillNnp = false;
    // отчет без агрегации
    protected $isFlat = true;

    // ************************************************************************
    // Overrides

    /**
     * Пре-обработка
     *
     * @throws \Exception
     */
    public function processBefore()
    {
        $this->prepareBaseForQuery();
        $this->prepareSelectsForQuery();
        $this->setTotalsDefaults();
    }

    /**
     * Получение данных
     *
     * @return array
     * @throws \yii\db\Exception
     */
    public function getItems()
    {
        $items = parent::getItems();

        $items = $this->makeUnique($items);

        $this->makeEagerLoading($items);

        return $items;
    }

    /**
     * Обработчик записи
     *
     * @param array $item
     * @throws \yii\db\Exception
     */
    public function processItem(array $item)
    {
        $this->processVoipRecord($item);
    }

    /**
     * Пост-обработка
     */
    public function processAfter()
    {
        $this->addTotals();
    }

    /**
     * Получение базового запроса для ридера
     *
     * @param int $connectionId
     * @return ActiveQuery
     */
    protected function getBaseQueryByConnectionId($connectionId)
    {
        $query = clone $this->query;

        // заполняем отдельно через жадную загрузку
        $this->isNeedFillNnp = true;
//        if ($connectionId == Config::CONNECTION_MAIN) {
//            $this->addNnpInfoInQuery($query);
//            $this->addNnpInfoInQuery($query, 'cr2_');
//        } else {
//            $this->isNeedFillNnp = true;
//        }

        return $query;
    }


    // ************************************************************************
    // Customs

    /**
     * @return bool
     */
    public function isFlat()
    {
        return $this->isFlat;
    }

    /**
     *
     * @throws \Exception
     */
    protected function prepareBaseForQuery()
    {
        $query = $this->query = $this->initQuery();

        $packages = $this->config->packages;
        $reportType = $this->config->type;

        if (isset($packages) && count($packages) > 0) {
            $query->andWhere(['in', 'cr.service_package_id', $packages]);
        }

        $offset = $this->config->from->getOffset();
        $query->select([]);
        switch ($reportType) {
            case Config::TYPE_DAY:
                $groupBy = new Expression("(cr.connect_time + '" . $offset . " second'::interval)::date");
                $query->addSelect([
                    'ts1' => $groupBy,
                ]);
                break;

            case Config::TYPE_MONTH:
                $groupBy = new Expression("to_char(cr.connect_time + '" . $offset . " second'::interval, 'YYYY-MM')");
                $query->addSelect([
                    'ts1' => $groupBy,
                ]);
                break;

            case Config::TYPE_YEAR:
                $groupBy = new Expression("to_char(cr.connect_time + '" . $offset . " second'::interval, 'YYYY')");
                $query->addSelect([
                    'ts1' => $groupBy,
                ]);
                break;

            default:
                $groupBy = '';
                $query->addSelect([
                    'ts1' => new Expression("cr.connect_time + '" . $offset . " second'::interval"),
                ]);
        }

        $this->isFlat = !$groupBy;
        if ($groupBy) {
            $query->groupBy([$groupBy]);

//            $this->config->from->setTime(0, 0, 0);
//            $this->config->to->setTime(0, 0, 0)->modify('+1 day');
        }

        if ($groupBy) {
            $query->orderBy('ts1 ASC');
        } else {
            $query->orderBy('ts1 ASC, cr.mcn_callid ASC, cr.orig DESC');
        }
    }

    /**
     *
     */
    protected function prepareSelectsForQuery()
    {
        $query = $this->query;

        $paidOnly = $this->config->paidOnly;

        if ($this->isFlat()) {
            $query->addSelect([
                'cr.id',
                'cr.src_number',
                'cr.nnp_city_id',
                'cr.nnp_is_mob',
                'cr.dst_number',
                'cr.orig',
                'cr.nnp_number_range_id',
            ]);

            if ($this->isWithProfit()) {
                $query->addSelect([
                    'cr2.id as cr2_id',
                    'cr2.src_number as cr2_src_number',
                    'cr2.nnp_city_id as cr2_nnp_city_id',
                    'cr2.nnp_is_mob as cr2_nnp_is_mob',
                    'cr2.dst_number as cr2_dst_number',
                    'cr2.orig as cr2_orig',
                    'cr2.nnp_number_range_id as cr2_nnp_number_range_id',
                ]);
            }
        }

        $createNumericField = function ($field, $fieldAggregated = 'SUM(%s)') {
            if (!$this->isFlat()) {
                if ($this->isWithProfit()) {
                    $fieldAggregated = 'SUM(CASE WHEN cr2.id IS NOT NULL THEN %s ELSE 0 END)';
                }

                $field = sprintf($fieldAggregated, $field);
            }

            return $field;
        };

        $query->addSelect([
            'price' => '-' . $createNumericField('cr.cost'),
            'ts2' => $createNumericField(
                $paidOnly ?
                    'CASE ABS(cr.cost) > 0.0001 WHEN true THEN cr.billed_time ELSE 0 END' :
                    'cr.billed_time'
            ),
            'cnt' => $this->isFlat() ?
                new Expression('1') :
                'COUNT(DISTINCT ' .
                    (
                        $paidOnly ?
                            '(CASE ABS(cr.cost) > 0.0001 WHEN true THEN cr.id ELSE NULL END)' :
                            'cr.id'
                    ) .
                    ')',
        ]);

        if ($this->isWithProfit()) {
            $query->addSelect([
                'ts22' => $createNumericField('cr2.billed_time'),

                'cost_price' => $createNumericField(
                    '(CASE WHEN cr.orig THEN COALESCE(cr2.cost, 0)' . $this->rateCur2 . $this->rateTax2 .
                        ' ELSE cr.cost' . $this->rateCur1  . $this->rateTax1 .
                        ' END)'
                ),
                'cost_price_with_tax' => $createNumericField(
                    '(CASE WHEN cr.orig THEN COALESCE(cr2.cost, 0)' . $this->rateCur2 . $this->rateTaxWith2 .
                        ' ELSE cr.cost' . $this->rateCur1  . $this->rateTaxWith1 .
                        ' END)'
                ),

                'price' => '-' . $createNumericField(
                    '(CASE WHEN cr.orig THEN cr.cost' . $this->rateCur1 . $this->rateTax1 .
                        ' ELSE COALESCE(cr2.cost, 0)' . $this->rateCur2 . $this->rateTax2 .
                        ' END)'
                ),
                'price_with_tax' => '-' . $createNumericField(
                    '(CASE WHEN cr.orig THEN cr.cost' . $this->rateCur1 . $this->rateTaxWith2 .
                        ' ELSE COALESCE(cr2.cost, 0)' . $this->rateCur2 . $this->rateTaxWith2 .
                        ' END)'
                ),
            ]);
        }

        if ($this->isFlat()) {
            $query->addSelect([
                'cr.nnp_package_minute_id',
                'cr.nnp_package_price_id',
                'cr.nnp_package_pricelist_id',
                'cr.package_time',
                'cr.billed_time',
                new Expression('cr.rate' . $this->rateCur1 . $this->rateTax1 . ' as rate'),
                new Expression('cr.rate' . $this->rateCur1 . $this->rateTaxWith1 . ' as rate_with_tax'),

                'cr.pricelist_id as pricelist_id',
            ]);

            if ($this->isWithProfit()) {
                $query->addSelect([
                    'cr2.nnp_package_minute_id as cr2_nnp_package_minute_id',
                    'cr2.nnp_package_price_id as cr2_nnp_package_price_id',
                    'cr2.nnp_package_pricelist_id as cr2_nnp_package_pricelist_id',
                    'cr2.package_time as cr2_package_time',
                    'cr2.billed_time as cr2_billed_time',
                    'COALESCE(cr2.rate, 0)' . $this->rateCur2 . $this->rateTax2 . ' as cr2_rate',
                    'COALESCE(cr2.rate, 0)' . $this->rateCur2 . $this->rateTaxWith2 . ' as cr2_rate_with_tax',

                    'cr2.account_version as cr2_account_version',
                    'cr2.account_id as cr2_account_id',
                    'cr2.pricelist_id as cr2_pricelist_id',
                ]);
            }
        }
    }

    /**
     *
     */
    protected function setTotalsDefaults()
    {
        $this->totals = ['price' => 0, 'ts2' => 0, 'cnt' => 0, 'is_total' => true];
        if ($this->isWithProfit()) {
            $this->totals['cost_price'] = 0;
            $this->totals['profit'] = 0;
        }
    }

    /**
     * @param array $items
     * @return array
     */
    protected function makeUnique(array $items)
    {
        if (!$this->isWithProfit()) {
            return $items;
        }

        $result = [];
        foreach ($items as $item) {
            $id = $item['id'] ? : $item['ts1'];
            if (!array_key_exists($id, $result) || $item['cr2_id']) {
                $result[$id] = $item;
            }
        }

        return $result;
    }

    /**
     * Жадная загрузка необходимых данных
     *
     * @param array $items
     */
    protected function makeEagerLoading(array $items)
    {
        $eagerConfig = [
            CallsRaw::class => [
                'fields' => ['id', 'cr2_id'],
                'values' => [],
                'primaryKey' => 'id',
                'db' => null,
            ],
            NumberRange::class => [
                'fields' => ['nnp_number_range_id', 'cr2_nnp_number_range_id'],
                'values' => [],
                'primaryKey' => 'id',
                'db' => Helper::getDbByConnectionId(Config::CONNECTION_MAIN),
            ],
        ];

        foreach ($items as $item) {
            foreach ($eagerConfig as $key => $config) {
                foreach ($config['fields'] as $field) {
                    if (!empty($item[$field])) {
                        $value = $item[$field];

                        $eagerConfig[$key]['values'][$value] = $value;
                    }
                }
            }
        }

        $this->fetched[CallsRaw::class] = [];
        $this->fetched[CallsRaw::class] = CallsRaw::find()
            ->with('operator')
            ->with('city.country')

            ->with('priceList')

            ->with('packageMinute.tariff')
            ->with('packageMinute.destination')

            ->with('packagePrice.tariff')
            ->with('packagePrice.destination')

            ->with('packagePriceList.tariff')
            ->with('packagePriceList.pricelist')
            ->with('packagePriceList.pricelistNnp')

            ->andWhere(['id' => $eagerConfig[CallsRaw::class]['values']])
            ->indexBy('id')
            ->all();

        $this->fetched[NumberRange::class] = [];
        $this->fetched[NumberRange::class] = (new Query())
            ->from(['nr' => NumberRange::tableName()])
            ->leftJoin(['nt' => NdcType::tableName()], 'nt.id = nr.ndc_type_id')
            ->leftJoin(['c' => City::tableName()], 'c.id = nr.city_id')
            ->leftJoin(['co' => Country::tableName()], 'co.code = nr.country_code')
            ->addSelect([
                'geo_name' =>
                    new Expression("
                                CASE WHEN 
                                        co.code = " . $this->getAccount()->contragent->country_id . " 
                                    THEN '' 
                                    ELSE " . ($this->getAccount()->contragent->country_id == Country::RUSSIA ? "co.name_rus" : "co.name_eng") . " || '. '
                                END  ||

                                CASE WHEN 
                                        c.id IS NULL 
                                    THEN '' 
                                    ELSE " . ($this->getAccount()->contragent->country_id == Country::RUSSIA ? "c.name" : "c.name_translit") . " 
                                END 
                                "),
                'ndc_type_name' => 'nt.name',
                'ndc_type_id' => 'nr.ndc_type_id',

                'id' => 'nr.id',
            ])
            ->where(['nr.id' => $eagerConfig[NumberRange::class]['values']])
            ->indexBy('id')
            ->all(Helper::getDbByConnectionId(Config::CONNECTION_MAIN));
    }

    /**
     * @param array $record
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    protected function processVoipRecord(array $record)
    {
        if ($this->isNeedFillNnp) {
            $this->addNnpInfoToRecord($record);
            $this->isWithProfit() && $this->addNnpInfoToRecord($record, 'cr2_');
        }

        $record['geo'] = $record['geo_name'];
        $record['operator'] .= ($record['ndc_type_name'] ? ' (' . $record['ndc_type_name'] . ')' : '');
        unset($record['geo_name'], $record['ndc_type_name'], $record['ndc_type_id']);

        $ts = new DateTime($record['ts1']);

        $record['tsf1'] = $ts->format(DateTimeZoneHelper::DATETIME_FORMAT);
        $record['mktime'] = $ts->getTimestamp();
        $record['is_total'] = false;

        if ($record['ts2'] >= 24 * 60 * 60) {
            $d2 = floor($record['ts2'] / (24 * 60 * 60));
        } else {
            $d2 = 0;
        }

        // добавление в тотал перед форматированием
        $price = $record['price'];
        $priceWithTax = $record['price_with_tax'];
        $this->totals['cnt'] += $record['cnt'];
        $this->totals['ts2'] += $record['ts2'];

        $record['tsf2'] = ($d2 ? $d2 . 'd ' : '') . gmdate('H:i:s', $record['ts2']);
        $record['price'] = number_format($price, $this->decimals, '.', '');

        if ($this->isWithProfit()) {
            $formattedZeroRate = number_format(0, $this->decimals, '.', '');
            $record['rate_zero'] = $formattedZeroRate;

            $record['cr2_geo'] = $record['cr2_geo_name'];
            $record['cr2_operator'] .= ($record['cr2_ndc_type_name'] ? ' (' . $record['cr2_ndc_type_name'] . ')' : '');
            unset($record['cr2_geo_name'], $record['cr2_ndc_type_name'], $record['cr2_ndc_type_id']);

            $isOrigLeft = $record['orig'];
            if ($record['orig']) {
                list($record['cr2_geo'], $record['geo']) = [$record['geo'], $record['cr2_geo']];
                list($record['cr2_operator'], $record['operator']) = [$record['operator'], $record['cr2_operator']];
            }

            $record = Helper::calcRow($this->getAccount(), $record, $this->isWithProfit());
            $costPrice = $record['cost_price'];

            $hasRight = $record['cr2_id'];
            // добавление в тотал перед форматированием
            if ($this->isFlat() && !$hasRight) {
                $costPrice = 0;
                $price = 0;
            }

            // добавление в тотал неформатированное
            $this->totals['cost_price'] += $costPrice;
            $this->totals['cost_price_with_tax'] += $record['cost_price_with_tax'];

            $record = Helper::formatRow($record, $this->isWithProfit(), $this->decimals);

            $record['tsf22'] = '';
            if (!$this->isFlat() || $hasRight) {
                if ($record['ts22'] >= 24 * 60 * 60) {
                    $d22 = floor($record['ts2'] / (24 * 60 * 60));
                } else {
                    $d22 = 0;
                }

                $record['tsf22'] = ($d22 ? $d22 . 'd ' : '') . gmdate('H:i:s', $record['ts22']);
            } elseif ($record['billed_time']) {
                // несклееный
                $record[$isOrigLeft ? 'cost_price' : 'price'] = '???';
                $record['profit'] = '???';
            } else {
                // незавершенный
                $record[$isOrigLeft ? 'cost_price' : 'price'] = '';
                $record['profit'] = '';
            }
        }

        // добавление в тотал неформатированное
        $this->totals['price'] += $price;
        $this->totals['price_with_tax'] += $priceWithTax;

        if ($this->isFlat()) {
            $this->addPackageDetails($record);
            $this->isWithProfit() && $this->addPackageDetails($record, 'cr2_');
        }

        $this->result[] = $record;
    }

    /**
     * Добавление тотала
     */
    protected function addTotals()
    {
        $totals = $this->totals;

        $totals['ts1'] = null;
        $totals['tsf1'] = 'Итого';
        $totals['src_number'] = 'Записей: ' . count($this->result);
        $totals['dst_number'] = '&nbsp;';

        if ($totals['ts2'] >= 24 * 60 * 60) {
            $d = floor($totals['ts2'] / (24 * 60 * 60));
        } else {
            $d = 0;
        }

        $totals['tsf2'] = ($d ? $d . 'd ' : '') . gmdate('H:i:s', $totals['ts2'] - $d * 24 * 60 * 60);

        $totals = Helper::calcRow($this->getAccount(), $totals, $this->isWithProfit());
        $totals = Helper::formatRow($totals, $this->isWithProfit());

        if(!$this->isWithProfit()) {
            if ($this->getAccount()->price_include_vat) {
                $totals['price'] = $totals['price_with_tax'] . ' (включая НДС)';
            } else {
                $totals['price'] = $totals . ' (<b>' . $totals['price_with_tax'] . ' - Сумма с НДС</b>)';
            }
        }

        $this->result['total'] = $totals;
    }

    /**
     * Добавляет к строке детализации звонка данные по использованным пакетам
     *
     * @param array $record
     * @param string $prefix префикс для полей
     */
    protected function addPackageDetails(&$record, $prefix = '')
    {
        $fieldName = function ($field) use ($prefix) {
            return $prefix . $field;
        };

        $hasSide = $record[$fieldName('id')];
        if ($hasSide) {
            $isOrig = $prefix ? !$record['orig'] : $record['orig'];
            $side = $isOrig ? 'right' : 'left';

            $version = $this->getAccount()->account_version;
            if ($prefix) {
                if (!empty($record[$fieldName('account_version')])) {
                    $version = $record[$fieldName('account_version')];
                } else {
                    $accountId = $record[$fieldName('account_id')];
                    if ($accountId && $account = ClientAccount::findOne(['id' => $accountId])) {
                        $version = $account->account_version;
                    }
                }
            }

            if ($version == ClientAccount::VERSION_BILLER_UNIVERSAL) {
                $this->addPackagesInfoV5($record, $side, $prefix);
            } else {
                $this->addPackagesInfoV4($record, $side, $prefix);
            }

            $record = $this->addPackagesCommonInfo($record, $side, $prefix);
        }

        unset(
            $record[$fieldName('nnp_package_minute_id')],
            $record[$fieldName('nnp_package_price_id')],
            $record[$fieldName('nnp_package_pricelist_id')],
            $record[$fieldName('package_time')],
            $record[$fieldName('billed_time')],
            $record[$fieldName('rate')],
            $record[$fieldName('rate_with_tax')],
            $record[$fieldName('pricelist_id')]
        );

        if ($prefix) {
            unset($record[$fieldName('account_version')]);
            unset($record[$fieldName('account_id')]);
        }
    }

    /**
     * Добавляет детализацию для клиента версии 4
     *
     * @param array $record
     * @param string $prefix
     * @param string $side
     */
    protected function addPackagesInfoV4(&$record, $side, $prefix)
    {
        $fieldName = function ($field) use ($prefix) {
            return $prefix . $field;
        };

        /** @var CallsRaw $callsRaw */
        $callsRaw = !empty($this->fetched[CallsRaw::class][$record[$fieldName('id')]]) ?
            $this->fetched[CallsRaw::class][$record[$fieldName('id')]] :
            null;

        // Детализация универсальных пакетов.
        if ($callsRaw) {
            $priceList = $callsRaw->priceList;
        } else {
            $priceList = $record[$fieldName('pricelist_id')] ?
                Pricelist::findOne([
                    'id' => $record[$fieldName('pricelist_id')]
                ]) : null;
        }

        /** @var Pricelist $priceList */
        if ($priceList) {
            $record[$side]['package_price'] = [
                'name' => $priceList->name,
                'taken' => 'none',
            ];
        }
    }

    /**
     * Добавляет детализацию для клиента версии 5
     *
     * @param array $record
     * @param string $prefix
     * @param string $side
     */
    protected function addPackagesInfoV5(&$record, $side, $prefix)
    {
        $fieldName = function ($field) use ($prefix) {
            return $prefix . $field;
        };

        /** @var CallsRaw $callsRaw */
        $callsRaw = !empty($this->fetched[CallsRaw::class][$record[$fieldName('id')]]) ?
            $this->fetched[CallsRaw::class][$record[$fieldName('id')]] :
            null;

        // Детализация универсальных пакетов.
        if ($callsRaw) {
            $packageMinute = $callsRaw->packageMinute;
        } else {
            $packageMinute = $record[$fieldName('nnp_package_minute_id')] ?
                PackageMinute::findOne([
                    'id' => $record[$fieldName('nnp_package_minute_id')]
                ]) : null;
        }

        /** @var PackageMinute $packageMinute */
        if ($packageMinute) {
            $record[$side]['package_minute'] = [
                'name' => $packageMinute->tariff->name,
                'minute' => $packageMinute->minute,
                'destination' => $packageMinute->destination->name,
                'taken' => 'none',
            ];
        }

        if ($callsRaw) {
            $packagePrice = $callsRaw->packagePrice;
        } else {
            $packagePrice = $record[$fieldName('nnp_package_price_id')] ?
                PackagePrice::findOne([
                    'id' => $record[$fieldName('nnp_package_price_id')]
                ]) : null;
        }

        /** @var PackagePrice $packagePrice */
        if ($packagePrice) {
            $record[$side]['package_price'] = [
                'name' => $packagePrice->tariff->name,
                'taken' => 'none',
            ];
            return;
        }

        if ($callsRaw) {
            $packagePriceList = $callsRaw->packagePriceList;
        } else {
            $packagePriceList = $record[$fieldName('nnp_package_pricelist_id')] ?
                PackagePricelist::findOne([
                    'id' => $record[$fieldName('nnp_package_pricelist_id')]
                ]) : null;
        }

        if ($packagePriceList) {
            if ($packagePriceList->pricelist_id) {
                $record[$side]['package_price'] = [
                    'name' => $packagePriceList->pricelist->name,
                    'taken' => 'none',
                ];
            } else {
                $record[$side]['package_price'] = [
                    'name' => $packagePriceList->pricelistNnp->name,
                    'taken' => 'none',
                ];
            }
        }
    }

    /**
     * @param array $record
     * @param string $side
     * @param string $prefix
     * @return mixed
     */
    protected function addPackagesCommonInfo(&$record, $side, $prefix)
    {
        $fieldName = function ($field) use ($prefix) {
            return $prefix . $field;
        };

        $formattedRate = number_format($record[$fieldName('rate')], $this->decimals, '.', '');
        $formattedRateWithTax = number_format($record[$fieldName('rate_with_tax')], $this->decimals, '.', '');

        $record[$side]['rate'] = $formattedRate;
        if ($formattedRateWithTax !== $formattedRate) {
            $record[$side]['rate_with_tax'] = $formattedRateWithTax;
        }

        if ($record[$fieldName('billed_time')]) {
            $isAllFromPackage = false;
            if ($record[$fieldName('package_time')]) {
                $isAllFromPackage =
                    $record[$fieldName('billed_time')] == $record[$fieldName('package_time')];

                if ($record[$side]['package_minute']) {
                    $record[$side]['package_minute']['taken'] = $isAllFromPackage ? 'all' : 'part';
                } else {
                    // используется пакет, но минутного пакета нет
                    $record[$side]['package_minute_price'] = [
                        'name' => $record[$side]['package_price']['name'] ?: 'не указан',
                        'taken' => $isAllFromPackage ? 'all' : 'part',
                    ];
                }
            }

            if (!$isAllFromPackage && $record[$side]['package_price']) {
                $record[$side]['package_price']['taken'] = 'all';
            }
        }

        $record[$side]['has_package_minutes'] = !empty($record[$side]['package_minute']) || !empty($record[$side]['package_minute_price']);

        return $record;
    }

    /**
     * Добавляем поля с NNP информацией
     *
     * @param Query $query
     * @param string $prefix
     * @return Query
     */
    protected function addNnpInfoInQuery(Query $query = null, $prefix = '')
    {
        $fieldName = function ($field) use ($prefix) {
            return $prefix . $field;
        };

        $aliasNr = $fieldName('nr');
        $aliasNt = $fieldName('nt');
        $aliasC = $fieldName('c');
        $aliasCo = $fieldName('co');
        // Для детализации по звонкам берем названия из NNP
        if ($query) {
            $aliasCr = $prefix ? 'cr2' : 'cr';
            $query->leftJoin([$aliasNr => NumberRange::tableName()], "{$aliasNr}.id = {$aliasCr}.nnp_number_range_id");
        } else {
            $query = (new Query())
                ->from([$aliasNr => NumberRange::tableName()]);
        }
        $query->leftJoin([$aliasNt => NdcType::tableName()], "{$aliasNt}.id = {$aliasNr}.ndc_type_id");
        $query->leftJoin([$aliasC => City::tableName()], "{$aliasC}.id = {$aliasNr}.city_id");
        $query->leftJoin([$aliasCo => Country::tableName()], "{$aliasCo}.code = {$aliasNr}.country_code");

        // показываем страну, если страна звонка и страна ЛС не совпадает
        // ставим точку после страны
        // показываем город, если есть
        $query->addSelect([
            $fieldName('geo_name') =>
                new Expression("
                
                CASE WHEN 
                        {$aliasCo}.code = " . $this->getAccount()->contragent->country_id . " 
                    THEN '' 
                    ELSE " . ($this->getAccount()->contragent->country_id == Country::RUSSIA ? "{$aliasCo}.name_rus" : "{$aliasCo}.name_eng") . " || '. '
                END  ||
                
                CASE WHEN 
                        {$aliasC}.id IS NULL 
                    THEN '' 
                    ELSE " . ($this->getAccount()->contragent->country_id == Country::RUSSIA ? "{$aliasC}.name" : "{$aliasC}.name_translit") . " 
                END 
                "),
            $fieldName('ndc_type_name') => "{$aliasNt}.name",
            $fieldName('ndc_type_id') => "{$aliasNr}.ndc_type_id",
        ]);

        return $query;
    }

    /**
     * Добавлем NNP информацию полученную отдельно от запроса
     *
     * @param $item
     * @param string $prefix
     * @throws \yii\db\Exception
     */
    protected function addNnpInfoToRecord(&$item, $prefix = '')
    {
        $fieldName = function ($field) use ($prefix) {
            return $prefix . $field;
        };

        $id = $item[$fieldName('nnp_number_range_id')];
        if (!array_key_exists($id, $this->fetched[NumberRange::class])) {
            $this->fetched[NumberRange::class][$id] = $this
                ->addNnpInfoInQuery()
                ->where(['nr.id' => $id])
                ->createCommand(Helper::getDbByConnectionId(Config::CONNECTION_MAIN))
                ->queryOne();
        }

        $cached = $this->fetched[NumberRange::class][$id];
        $item[$fieldName('geo_name')] = $cached['geo_name'];
        $item[$fieldName('ndc_type_name')] = $cached['ndc_type_name'];
        $item[$fieldName('ndc_type_id')] = $cached['ndc_type_id'];

        /** @var CallsRaw $callsRaw */
        $callsRaw = !empty($this->fetched[CallsRaw::class][$item[$fieldName('id')]]) ?
            $this->fetched[CallsRaw::class][$item[$fieldName('id')]] :
            null;

        $item[$fieldName('operator')] = '';
        if ($callsRaw) {
            if ($country = $callsRaw->city->country->name) {
                $item[$fieldName('geo_name')] = sprintf("%s, %s", $country, $item[$fieldName('geo_name')]);
            }
            $item[$fieldName('operator')] = $callsRaw->operator->name;
        }
    }
}