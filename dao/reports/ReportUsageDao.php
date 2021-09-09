<?php

namespace app\dao\reports;

use app\models\ClientCounter;
use app\models\Currency;
use app\models\UsageTrunk;
use app\models\UsageVoip;
use app\modules\nnp\models\AccountTariffLight;
use app\modules\nnp\models\City;
use app\modules\nnp\models\Country;
use app\modules\nnp\models\NdcType;
use app\modules\nnp\models\NumberRange;
use app\modules\nnp\models\Package;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\Tariff;
use Yii;
use DateTime;
use DateTimeZone;
use yii\db\ActiveQuery;
use yii\db\Expression;
use app\classes\Singleton;
use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\billing\CallsRaw;
use app\models\billing\Geo;
use app\models\billing\InstanceSettings;
use app\models\UsageVoipPackage;
use app\modules\nnp\models\PackageMinute;
use app\modules\nnp\models\PackagePrice;
use app\modules\nnp\models\PackagePricelist;
use DateTimeImmutable;
use yii\db\Query;

/**
 * @method static ReportUsageDao me($args = null)
 */
class ReportUsageDao extends Singleton
{

    const REPORT_MAX_ITEMS = 50000;
    const REPORT_MAX_VIEW_ITEMS = 5000;

    const CONNECT_MAIN_AND_FAST = 1;
    const CONNECT_SLOW_AND_BIG = 2;

    const DETALITY_DEST = 'dest';
    const DETALITY_PACKAGE = 'package';
    const DETALITY_CALL = 'call';

    const DETALITY_DAY = 'day';
    const DETALITY_MONTH = 'month';
    const DETALITY_YEAR = 'year';

    /** @var ClientAccount */
    private $_account = null;

    private $_isFull = false;

    private $_isNeedFillNnp = false;

    /**
     * Статистика по телефонии
     *
     * @param string $region
     * @param string $from
     * @param string $to
     * @param string $detality
     * @param int $accountId
     * @param array $usages
     * @param int $paidonly
     * @param string $destination
     * @param string $direction
     * @param bool|false $isFull
     * @param array $packages
     * @param string $timeZone
     * @param int $tariffId
     * @return array
     */
    public function getUsageVoipStatistic(
        $region,
        $from,
        $to,
        $detality,
        $accountId,
        $usages = [],
        $paidonly = 0,
        $destination = 'all',
        $direction = 'both',
        $isFull = false,
        $packages = [],
        $timeZone = null,
        $tariffId = null
    )
    {
        $this->_account = ClientAccount::findOne(['id' => $accountId]);
        $this->_isFull = $isFull;

        !$timeZone && $timeZone = $this->_account->timezone_name;

        $from = (new DateTimeImmutable('now', new DateTimeZone($timeZone)))
            ->setTimestamp($from)
            ->setTime(0, 0, 0);

        $to = (new DateTimeImmutable('now', new DateTimeZone($timeZone)))
            ->setTimestamp($to)
            ->setTime(23, 59, 59);

        $query = CallsRaw::find()
            ->alias('cr')
            ->andWhere(['account_id' => $accountId]);

        // скрываем технические записи звонков
        $query->andWhere('NOT COALESCE(cr.cost=0 and cr.leg_type IN (2, 3) AND cr.sim_imsi_profile_id IN (1, 5), false)');

        $direction !== 'both' && $query->andWhere(['cr.orig' => ($direction === 'in' ? 'false' : 'true')]);
        isset($usages) && count($usages) > 0 && $query->andWhere([($region == 'trunk' ? 'trunk_service_id' : 'number_service_id') => $usages]);
        $paidonly && $query->andWhere('ABS(cr.cost) > 0.0001');

        $region == 'trunk' && $query->andWhere(['number_service_id' => null]); // статистика по транкам - смотрится по транкам. Звонки по услугам могут быть привязаны к мультитранкам.

        // Если есть мультитанк, то фильтруем входящие по транку клиента
        ClientAccount::dao()->isMultitrunkAccount($accountId) && $query
            ->andWhere([
                'OR',
                'cr.orig',
                [
                    'trunk_service_id' => UsageTrunk::find()
                        ->andWhere(['client_account_id' => $accountId])
                        ->select('id')
                        ->column()
                ]
            ]);

        if ($destination !== 'all') {
            $destinationArr = explode('-', $destination);

            $dest = $destinationArr[0];
            $mobile = $destinationArr[1];
            $zone = $destinationArr[2];

            if ((int)$dest == 0) {
                $query->andWhere(['<=', 'cr.destination_id', (int)$dest]);
            } else {
                $query->andWhere(['cr.destination_id' => (int)$dest]);
            }

            switch ($mobile) {
                case 'm':
                    $query->andWhere('cr.mob');
                    break;
                case 'f':
                    $query->andWhere('NOT cr.mob');
                    break;
            }

            if ((int)$dest == 0 && $mobile === 'f') {
                $query
                    ->leftJoin(['iss' => InstanceSettings::tableName()], 'iss.city_geo_id = cr.geo_id')
                    ->leftJoin(['g' => Geo::tableName()], 'g.id = iss.city_geo_id');

                switch ($zone) {
                    case 'z':
                        $query->andWhere('g.id IS NULL');
                        break;
                    default:
                        $query->andWhere('g.id IS NOT NULL');
                        break;
                }
            }
        }

        switch ($detality) {
            case self::DETALITY_DEST:
                return $this->_voipStatisticByDestination($query, $from, $to);
                break;
            case self::DETALITY_PACKAGE:
                return $this->_voipStatisticByPackage($query, $from, $to, $tariffId);
                break;
            default:
                return $this->_voipStatistic($query, $from, $to, $packages, $detality, $paidonly, $tariffId);
                break;
        }
    }

    /**
     * Статистика по пакетам
     *
     * @param int $usageId
     * @param int $packageId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getUsageVoipPackagesStatistic($usageId, $packageId = 0)
    {
        $query = UsageVoipPackage::find()
            ->actual()
            ->andWhere(['usage_voip_id' => $usageId]);

        if ((int)$packageId) {
            $query->andWhere(['id' => $packageId]);
        }

        return $query;
    }

    /**
     * Вспомогательная функция статистики по телефонии
     *
     * @param ActiveQuery $query
     * @param DateTimeImmutable $from
     * @param DateTimeImmutable $to
     * @param array $packages
     * @param string $detality
     * @param int $paidOnly
     * @param int $tariffId
     * @return array
     * @throws \Exception
     */
    private function _voipStatistic(
        ActiveQuery $query,
        DateTimeImmutable $from,
        DateTimeImmutable $to,
        $packages = [],
        $detality = '',
        $paidOnly = 0,
        $tariffId = null
    )
    {
        $offset = $from->getOffset();

        if (isset($packages) && count($packages) > 0) {
            $query->andWhere(['in', 'cr.service_package_id', $packages]);
        }

        if ($tariffId) {
            $query->leftJoin(['l' => AccountTariffLight::tableName()], 'l.id = cr.account_tariff_light_id');
            $query->andWhere(['l.tariff_id' => $tariffId]);
        }

        switch ($detality) {
            case self::DETALITY_DAY:
            case self::DETALITY_MONTH:
            case self::DETALITY_YEAR:
            {
                $groupBy = new Expression("DATE_TRUNC('" . $detality . "', cr.connect_time + '" . $offset . " second'::interval)");
                $query->addSelect([
                    'ts1' => new Expression("DATE_TRUNC('" . $detality . "', cr.connect_time + '" . $offset . " second'::interval)"),
                ]);
                break;
            }
            case self::DETALITY_CALL:
            {
                $groupBy = '';
                $query->addSelect([
                    'ts1' => new Expression("cr.connect_time + '" . $offset . " second'::interval"),
                ]);
                break;
            }

            default:
            {
                throw new \LogicException('Impossible call parameter');
            }
        }

        if ($groupBy) {
            $query->groupBy([$groupBy]);
        }

        if (!$groupBy) {
            $query->addSelect([
                'cr.id',
                'cr.src_number',
                'cr.nnp_city_id',
                'cr.nnp_is_mob',
                'cr.dst_number',
                'cr.orig',
                'cr.nnp_number_range_id',
                'location_id',
            ]);
        }

        $query->addSelect([
            'price' => ($groupBy ? '-SUM' : '-') . '(cr.cost)',
            'ts2' => ($groupBy ? 'SUM' : '') . '(' . ($paidOnly ? 'CASE ABS(cr.cost) > 0.0001 WHEN true THEN cr.billed_time ELSE 0 END' : 'cr.billed_time') . ')',
            'cnt' => $groupBy ? 'SUM(' . ($paidOnly ? 'CASE ABS(cr.cost) > 0.0001 WHEN true THEN 1 ELSE 0 END' : new Expression('1')) . ')' : new Expression('1'),
        ]);

        $isWithPackageDetail = false;
        if (!$groupBy && $this->_account->account_version == ClientAccount::VERSION_BILLER_UNIVERSAL) {
            $isWithPackageDetail = true;

            $query->addSelect([
                'nnp_package_minute_id',
                'nnp_package_price_id',
                'nnp_package_pricelist_id',
                'package_time',
                'billed_time',
                'rate',
            ]);
        }


        $query->orderBy('ts1 ASC');


        $rt = ['price' => 0, 'ts2' => 0, 'cnt' => 0, 'is_total' => true];
        $decimals = in_array($this->_account->currency, [Currency::USD, Currency::EUR]) ? 6 : 2;

        $callBackProcessRecord = function ($record) use ($isWithPackageDetail, $decimals, &$result, &$rt) {
            $record['geo'] = '';
            if (isset($record['geo_name']) && isset($record['ndc_type_id'])) {
                $record['geo'] = $record['geo_name'] . ($record['ndc_type_id'] != NdcType::ID_GEOGRAPHIC && $record['ndc_type_name'] ? ' (' . $record['ndc_type_name'] . ')' : '');
            }
            unset($record['geo_name'], $record['ndc_type_name'], $record['ndc_type_id']);

            $ts = new DateTime($record['ts1']);

            $record['tsf1'] = $ts->format(DateTimeZoneHelper::DATETIME_FORMAT);
            $record['mktime'] = $ts->getTimestamp();
            $record['is_total'] = false;

            if ($record['ts2'] >= 24 * 60 * 60) {
                $d = floor($record['ts2'] / (24 * 60 * 60));
            } else {
                $d = 0;
            }

            // добавление в тотал перед форматирование
            $rt['price'] += $record['price'];
            $rt['cnt'] += $record['cnt'];
            $rt['ts2'] += $record['ts2'];

            $record['tsf2'] = ($d ? $d . 'd ' : '') . gmdate('H:i:s', $record['ts2']);
            $record['price'] = number_format($record['price'], $decimals, '.', '');

            if ($isWithPackageDetail) {
                $this->_admixedPackageDetails($record);
            }

            $record['location_name'] = '';
            if (isset($record['location_id']) && $record['location_id']) {
                $locationList = Package::getListLocation(false);
                $record['location_name'] = $locationList[$record['location_id']] ?? '' ;
            }
            unset($record['location_id']);

            $result[] = $record;
        };

        $this->_processRecords($query, $from, $to, $callBackProcessRecord, $detality);

        $rt['ts1'] = null;
        $rt['tsf1'] = 'Итого';
        $rt['num_to'] = '&nbsp;';
        $rt['num_from'] = '&nbsp;';

        if ($rt['ts2'] >= 24 * 60 * 60) {
            $d = floor($rt['ts2'] / (24 * 60 * 60));
        } else {
            $d = 0;
        }

        $rt['tsf2'] = ($d ? $d . 'd ' : '') . gmdate('H:i:s', $rt['ts2'] - $d * 24 * 60 * 60);
        $rt = $this->_getTotalPrices($rt);

        $result['total'] = $rt;

        return $result;
    }

    /**
     * Получение коннектора по id
     *
     * @param integer $connectId
     * @return \yii\db\Connection
     */
    private function _getConnectingById($connectId)
    {
        if ($connectId == self::CONNECT_MAIN_AND_FAST) {
            return CallsRaw::getDb();
        }

        return Yii::$app->dbPgStatistic;
    }

    /**
     * Создаем Reader
     *
     * @param Query $queryOrig
     * @param DateTimeImmutable $from
     * @param DateTimeImmutable $to
     * @param integer $connectId
     * @param integer $inCount
     * @param boolean $isByCalls
     * @return \yii\db\DataReader
     */
    public function _makeReader(
        Query $queryOrig,
        DateTimeImmutable $from,
        DateTimeImmutable $to,
        $connectId,
        &$inCount,
        $isByCalls
    )
    {
        $db = $this->_getConnectingById($connectId);

        $limit = $this->_isFull ? self::REPORT_MAX_ITEMS : self::REPORT_MAX_VIEW_ITEMS;

        if (($limit - $inCount) <= 0) { // лимит получения записей исчерпан
            return null;
        }

        $limit -= $inCount;

        $query = clone $queryOrig;

        if ($isByCalls) {
            if ($connectId == self::CONNECT_MAIN_AND_FAST) {
                $this->_addNnpInfoInQuery($query);
            } else {
                $this->_isNeedFillNnp = true;
            }
        }

        $query
            ->andWhere([
                'BETWEEN',
                'cr.connect_time',
                $from->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC))->format(DateTimeZoneHelper::DATETIME_FORMAT),
                $to->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC))->format(DateTimeZoneHelper::DATETIME_FORMAT . '.999999')
            ])
            ->limit($limit)
            ->createCommand($db);


        $countAll = $query->count();
        $inCount += $countAll >= $limit ? $limit : $countAll;

        if ($inCount >= $limit) {
            Yii::$app->session->setFlash('error',
                'Статистика отображается не полностью.' .
                Html::tag('br') . PHP_EOL .
                ' Сделайте ее менее детальной или сузьте временной период'
            );
        }

        return $query->createCommand($db)->query();
    }

    /**
     * Получение даты разделения статистики
     *
     * @return DateTimeImmutable
     */
    public static function _getSeparationDate()
    {
        $query = CallsRaw::getDb()
            ->createCommand('SELECT tablename FROM pg_tables WHERE (tablename LIKE \'calls_raw_20%\') ORDER BY tablename ASC LIMIT 1')
            ->queryScalar();

        if (!$query || !preg_match('/calls_raw_(\d{4})(\d{2})/', $query, $matches)) {
            throw new \LogicException('Невозможно получить данные с основного сервера');
        }

        return (new DateTimeImmutable(
                $matches[1] . '-' . $matches[2] . '-01 00:00:00',
                new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC)
        ));

    }

    /**
     * Получение, разбивка и обработка данных статистики
     *
     * @param Query $query
     * @param DateTimeImmutable $from
     * @param DateTimeImmutable $to
     * @param callable $callBackRecordProcessor
     */
    private function _processRecords(Query $query, DateTimeImmutable $from, DateTimeImmutable $to, $callBackRecordProcessor, $detality)
    {
        $isByCalls = $detality == self::DETALITY_CALL;

        $dateStatisticsSeparation = $this->_getSeparationDate();
        $callCount = 0;

        // разбиваем период на части
        if ($from < $dateStatisticsSeparation && $to > $dateStatisticsSeparation) {
            $query1 = $this->_makeReader($query, $from, $dateStatisticsSeparation, self::CONNECT_SLOW_AND_BIG, $callCount, $isByCalls);
            $query2 = $this->_makeReader($query, $dateStatisticsSeparation, $to, self::CONNECT_MAIN_AND_FAST, $callCount, $isByCalls);
        } else {
            $connector = $from >= $dateStatisticsSeparation ? self::CONNECT_MAIN_AND_FAST : self::CONNECT_SLOW_AND_BIG;
            $query1 = $this->_makeReader($query, $from, $to, $connector, $callCount, $isByCalls);
            $query2 = null;
        }


        foreach ($query1 as $record) {
            if ($this->_isNeedFillNnp) {
                $this->_addNnpInfoInRecord($record);
            }
            $callBackRecordProcessor($record);
        }

        if ($query2) {
            foreach ($query2 as $record) {
                if ($this->_isNeedFillNnp) {
                    $this->_addNnpInfoInRecord($record);
                }
                $callBackRecordProcessor($record);
            }
        }
    }

    /**
     * Добавляем поля с NNP информацией
     *
     * @param Query $query
     * @return Query
     */
    private function _addNnpInfoInQuery(Query $query = null)
    {
        // Для детализации по звонкам берем названия из NNP
        if ($query) {
            $query->leftJoin(['nr' => NumberRange::tableName()], 'nr.id = cr.nnp_number_range_id');
        } else {
            $query = (new Query())
                ->from(['nr' => NumberRange::tableName()]);
        }
        $query->leftJoin(['nt' => NdcType::tableName()], 'nt.id = nr.ndc_type_id');
        $query->leftJoin(['c' => City::tableName()], 'c.id = nr.city_id');
        $query->leftJoin(['co' => Country::tableName()], 'co.code = nr.country_code');

        // показываем страну, если страна звонка и страна ЛС не совпадает
        // ставим точку после страны
        // показываем город, если есть
        $query->addSelect([
            'geo_name' =>
                new Expression("
                
                CASE WHEN 
                        co.code = " . $this->_account->contragent->country_id . " 
                    THEN '' 
                    ELSE " . ($this->_account->contragent->country_id == Country::RUSSIA ? "co.name_rus" : "co.name_eng") . " || '. '
                END  ||
                
                CASE WHEN 
                        c.id IS NULL 
                    THEN '' 
                    ELSE " . ($this->_account->contragent->country_id == Country::RUSSIA ? "c.name" : "c.name_translit") . " 
                END 
                "),
            'ndc_type_name' => 'nt.name',
            'ndc_type_id' => 'nr.ndc_type_id',
        ]);

        return $query;
    }

    /**
     * Добавлем NNP информацию полученную отдельно от запроса
     *
     * @param array $record
     */
    private function _addNnpInfoInRecord(&$record)
    {
        static $cache = [];

        if (!array_key_exists($record['nnp_number_range_id'], $cache)) {
            $cache[$record['nnp_number_range_id']] = $this
                ->_addNnpInfoInQuery()
                ->where(['nr.id' => $record['nnp_number_range_id']])
                ->createCommand($this->_getConnectingById(self::CONNECT_MAIN_AND_FAST))
                ->queryOne();

        }

        $record['geo_name'] = $cache[$record['nnp_number_range_id']]['geo_name'];
        $record['ndc_type_name'] = $cache[$record['nnp_number_range_id']]['ndc_type_name'];
        $record['ndc_type_id'] = $cache[$record['nnp_number_range_id']]['ndc_type_id'];
    }


    /**
     * Вспомогательная функция. Статистика по направлениям
     *
     * @param ActiveQuery $query
     * @return array
     */
    private function _voipStatisticByDestination(ActiveQuery $query, DateTimeImmutable $from, DateTimeImmutable $to)
    {
        $query->select([
            'dest' => 'cr.destination_id',
            'cr.mob',
            'price' => '-SUM(cr.cost)',
            'len' => 'SUM(cr.billed_time)',
            'cnt' => 'SUM(1)'
        ]);

        $query->groupBy(['cr.destination_id', 'mob']);

        $result = [
            'mos_loc' => [
                'tsf1' => 'Местные Стационарные',
                'cnt' => 0,
                'len' => 0,
                'price' => 0,
                'is_total' => false
            ],
            'mos_mob' => [
                'tsf1' => 'Местные Мобильные',
                'cnt' => 0,
                'len' => 0,
                'price' => 0,
                'is_total' => false
            ],
            'rus_fix' => [
                'tsf1' => 'Россия Стационарные',
                'cnt' => 0,
                'len' => 0,
                'price' => 0,
                'is_total' => false
            ],
            'rus_mob' => [
                'tsf1' => 'Россия Мобильные',
                'cnt' => 0,
                'len' => 0,
                'price' => 0,
                'is_total' => false
            ],
            'int' => [
                'tsf1' => 'Международка',
                'cnt' => 0,
                'len' => 0,
                'price' => 0,
                'is_total' => false
            ],
        ];

        $callBackRecordProcessing = function ($record) use (&$result) {
            if ($record['dest'] <= 0 && $record['mob'] === false) {
                $result['mos_loc']['len'] += $record['len'];
                $result['mos_loc']['price'] += $record['price'];
                $result['mos_loc']['cnt'] += $record['cnt'];
            } elseif ($record['dest'] <= 0 && $record['mob'] === true) {
                $result['mos_mob']['len'] += $record['len'];
                $result['mos_mob']['price'] += $record['price'];
                $result['mos_mob']['cnt'] += $record['cnt'];
            } elseif ($record['dest'] == 1 && $record['mob'] === false) {
                $result['rus_fix']['len'] += $record['len'];
                $result['rus_fix']['price'] += $record['price'];
                $result['rus_fix']['cnt'] += $record['cnt'];
            } elseif ($record['dest'] == 1 && $record['mob'] === true) {
                $result['rus_mob']['len'] += $record['len'];
                $result['rus_mob']['price'] += $record['price'];
                $result['rus_mob']['cnt'] += $record['cnt'];
            } elseif ($record['dest'] == 2 || $record['dest'] == 3) {
                $result['int']['len'] += $record['len'];
                $result['int']['price'] += $record['price'];
                $result['int']['cnt'] += $record['cnt'];
            }
        };

        $this->_processRecords($query, $from, $to, $callBackRecordProcessing, self::DETALITY_DEST);

        $cnt = 0;
        $len = 0;
        $price = 0;
        foreach ($result as $destination => $data) {
            $cnt += $data['cnt'];
            $len += $data['len'];
            $price += $data['price'];

            $delta = 0;
            if ($data['len'] >= 24 * 60 * 60) {
                $delta = floor($data['len'] / (24 * 60 * 60));
            }

            $result[$destination]['tsf2'] = ($delta ? $delta . 'd ' : '') . gmdate('H:i:s',
                    $data['len'] - $delta * 24 * 60 * 60);
            $result[$destination]['price'] = number_format($data['price'], 2, '.', '');
        }

        $delta = 0;
        $total_row = [
            'is_total' => true,
            'tsf1' => 'Итого',
            'price' => $price,
        ];

        if ($len >= 24 * 60 * 60) {
            $delta = floor($len / (24 * 60 * 60));
        }

        $total_row['tsf2'] = ($delta ? $delta . 'd ' : '') . gmdate('H:i:s', $len - $delta * 24 * 60 * 60);
        $total_row = $this->_getTotalPrices($total_row);
        $total_row['cnt'] = $cnt;

        $result['total'] = $total_row;

        return $result;
    }

    /**
     * Вспомогательная функция. Статистика по пакетам
     *
     * @param ActiveQuery $query
     * @param DateTimeImmutable $from
     * @param DateTimeImmutable $to
     * @param int $tariffId
     * @return array
     */
    private function _voipStatisticByPackage(ActiveQuery $query, DateTimeImmutable $from, DateTimeImmutable $to, $tariffId = null)
    {
        $query->leftJoin(['l' => AccountTariffLight::tableName()], 'l.id = cr.account_tariff_light_id');

        $query->select([
            'price' => '-SUM(cr.cost)',
            'len' => 'SUM(cr.billed_time)',
            'cnt' => 'SUM(1)'
        ]);
        $query->addSelect(['l.tariff_id', 'l.account_package_id']);

        $query->groupBy(['l.tariff_id', 'l.account_package_id']);
        $query->orderBy(['l.account_package_id' => SORT_ASC, 'l.tariff_id' => SORT_ASC]);

        $tariffId && $query->andWhere(['l.tariff_id' => $tariffId]);

        $result = [];

        $callBackRecordProcessing = function ($record) use (&$result) {
            static $cache = ['' => '---- Бесплатные вызовы ----'];

            if (!isset($cache[$record['tariff_id']])) {
                $cache[$record['tariff_id']] = Tariff::find()->where(['id' => $record['tariff_id']])->select(['name'])->scalar();
            }

            $record['tsf1'] = $cache[$record['tariff_id']];
            $result[] = $record;
        };

        $this->_processRecords($query, $from, $to, $callBackRecordProcessing, self::DETALITY_DEST);

        $cnt = 0;
        $len = 0;
        $price = 0;
        foreach ($result as &$data) {
            $cnt += $data['cnt'];
            $len += $data['len'];
            $price += $data['price'];

            $delta = 0;
            if ($data['len'] >= 24 * 60 * 60) {
                $delta = floor($data['len'] / (24 * 60 * 60));
            }

            $data['tsf2'] = ($delta ? $delta . 'd ' : '') . gmdate('H:i:s',
                    $data['len'] - $delta * 24 * 60 * 60);
            $data['price'] = number_format($data['price'], 2, '.', '');
        }

        $delta = 0;
        $total_row = [
            'is_total' => true,
            'tsf1' => 'Итого',
            'price' => $price,
            'cnt' => $cnt,
        ];

        if ($len >= 24 * 60 * 60) {
            $delta = floor($len / (24 * 60 * 60));
        }

        $total_row['tsf2'] = ($delta ? $delta . 'd ' : '') . gmdate('H:i:s', $len - $delta * 24 * 60 * 60);
        $total_row = $this->_getTotalPrices($total_row);

        $result['total'] = $total_row;

        return $result;
    }

    /**
     * Формирование итоговых значений
     *
     * @param array $row
     * @return array
     */
    private function _getTotalPrices(array $row = [])
    {
        $taxRate = $this->_account->getTaxRate();

        if ($this->_account->price_include_vat) {
            $row['price_without_tax'] = number_format($row['price'] * 100 / (100 + $taxRate), 2, '.', '');
            $row['price_with_tax'] = number_format($row['price'], 2, '.', '');
            $row['price'] = $row['price_with_tax'] . ' (включая НДС)';
        } else {
            $row['price_without_tax'] = number_format($row['price'], 2, '.', '');
            $row['price_with_tax'] = number_format($row['price'] * (100 + $taxRate) / 100, 2, '.', '');
            $row['price'] = $row['price_without_tax'] . ' (<b>' . $row['price_with_tax'] . ' - Сумма с НДС</b>)';
        }

        return $row;
    }

    private function _getCachedValue($key, $id)
    {
        static $c = [];

        if (!$id) {
            return null;
        }

        if (isset($c[$key][$id])) {
            return $c[$key][$id];
        }

        $value = null;

        switch ($key) {
            case 'nnp_package_minute_id':
                $value = PackageMinute::find()
                    ->where(['id' => $id])
                    ->with(['tariff', 'destination'])
                    ->one();
                break;

            case 'nnp_package_price_id':
                $value = PackagePrice::find()
                    ->where(['id' => $id])
                    ->with(['tariff', 'destination'])
                    ->one();
                break;

            case 'nnp_package_pricelist_id':
                $value = PackagePricelist::find()
                    ->where(['id' => $id])
                    ->with(['tariff', 'pricelist', 'pricelistNnp'])
                    ->one();
                break;

            default:
                throw new \InvalidArgumentException('Invalid key: ' . $key);
        }

        $c[$key][$id] = $value;

        return $value;
    }

    /**
     * Добавляет к строке детализации звонка данные по использованным пакетам
     *
     * @param array $record
     */
    private function _admixedPackageDetails(&$record)
    {
        // Детализация универсальных пакетов.
        /** @var PackageMinute $packageMinute */
        $packageMinute = $this->_getCachedValue('nnp_package_minute_id', $record['nnp_package_minute_id']);
        if ($packageMinute) {
            $record['package_minute'] = [
                'name' => $packageMinute->tariff->name,
                'minute' => $packageMinute->minute,
                'destination' => $packageMinute->destination->name,
                'taken' => 'none',
            ];
        }

        /** @var PackagePrice $packagePrice */
        $packagePrice = $this->_getCachedValue('nnp_package_price_id', $record['nnp_package_price_id']);
        if ($packagePrice) {
            $record['package_price'] = [
                'name' => $packagePrice->tariff->name,
                'price' => $packagePrice->price,
                'destination' => $packagePrice->destination->name,
                'taken' => 'none',
            ];
        }

        /** @var PackagePricelist $packagePriceList */
        $packagePriceList = $this->_getCachedValue('nnp_package_pricelist_id', $record['nnp_package_pricelist_id']);
        if ($packagePriceList) {
            if ($packagePriceList->pricelist_id) {
                $record['package_pricelist'] = [
                    'name' => $packagePriceList->tariff->name,
                    'pricelist' => $packagePriceList->pricelist->name,
                    'taken' => 'none',
                ];
            } else {
                $record['package_pricelist_nnp'] = [
                    'name' => $packagePriceList->tariff->name,
                    'pricelist' => $packagePriceList->pricelistNnp->name,
                    'rate' => $record['rate'],
                    'taken' => 'none',
                ];
            }
        }

        if ($record['billed_time']) {
            $isAllFromPackage = false;
            if ($record['package_time']) {
                if ($record['billed_time'] == $record['package_time']) {
                    $record['package_minute']['taken'] = 'all';
                    $isAllFromPackage = true;
                } elseif ($record['billed_time'] > $record['package_time']) {
                    $record['package_minute']['taken'] = 'part';
                }
            }

            if (!$isAllFromPackage) {
                if (isset($record['package_price'])) {
                    $record['package_price']['taken'] = 'all';
                } elseif (isset($record['package_pricelist'])) {
                    $record['package_pricelist']['taken'] = 'all';
                } elseif (isset($record['package_pricelist_nnp'])) {
                    $record['package_pricelist_nnp']['taken'] = 'all';
                }
            }
        }

        unset(
            $record['nnp_package_minute_id'],
            $record['nnp_package_price_id'],
            $record['nnp_package_pricelist_id'],
            $record['package_time'],
            $record['real_price'],
            $record['real_cost'],
            $record['billed_time'],
            $record['rate']
        );
    }

    /**
     * Список услуг телефонии в ЛС
     *
     * @param ClientAccount $account
     * @return array
     */
    public function getUsageVoipAndTrunks(ClientAccount $account)
    {
        $usages = [];

        $trunks = UsageTrunk::find()
            ->select(['id', 'trunk_id'])
            ->where(['client_account_id' => $account->id])
            ->asArray()
            ->all();

        if ($account->account_version == ClientAccount::VERSION_BILLER_USAGE) {
            $usages = UsageVoip::find()
                ->alias('u')
                ->select([
                    'id' => 'u.id',
                    'phone_num' => 'u.E164',
                    'region' => 'u.region',
                    'region_name' => 'r.name',
                    'timezone_name' => 'r.timezone_name'
                ])
                ->joinWith('connectionPoint r')
                ->client($account->client)
                ->orderBy([
                    'u.region' => SORT_DESC,
                    'u.id' => SORT_ASC
                ])
                ->asArray()
                ->all();

        } elseif ($account->account_version == ClientAccount::VERSION_BILLER_UNIVERSAL) {

            $accountTariffs = AccountTariff::find()
                ->where([
                    'client_account_id' => $account->id,
                    'service_type_id' => ServiceType::ID_VOIP
                ])
                ->with('number.regionModel')
                ->orderBy([
                    AccountTariff::tableName() . '.id' => SORT_ASC
                ]);

            $usages = [];

            /** @var AccountTariff $accountTariff */
            foreach ($accountTariffs->each(1000) as $accountTariff) {
                $region = $accountTariff->number->regionModel;
                $usages[] = [
                    'id' => $accountTariff->id,
                    'phone_num' => $accountTariff->voip_number,
                    'region' => $region->id,
                    'region_name' => $region->name,
                    'timezone_name' => $region->timezone_name
                ];
            }

            /**
             * ->orderBy([
             *     'region'                     => SORT_DESC,
             *     'account_tariff.voip_number' => SORT_ASC,
             * ]);
             */
            usort($usages, function ($a, $b) {
                if ($a['region'] == $b['region']) {
                    if ($a['phone_num'] == $b['phone_num']) {
                        return 0;
                    }

                    return $a['phone_num'] > $b['phone_num'] ? 1 : -1;
                }

                return $a['region'] < $b['region'] ? 1 : -1;
            });
        }

        return [
            'voip' => $usages,
            'trunk' => $trunks
        ];
    }

    /**
     * Получение таймзон услуг
     *
     * @param ClientAccount $account
     * @param array $usageVoip
     * @return array
     */
    public function getTimezones(ClientAccount $account, $usageVoip)
    {
        $timezones = [
            $account->timezone_name => 1,
            DateTimeZoneHelper::TIMEZONE_UTC => 1
        ];

        foreach ($usageVoip as $usage) {
            $timezones[$usage['timezone_name']] = 1;
        }

        return array_keys($timezones);
    }

    public function getTariffs(ClientAccount $account)
    {
        return AccountTariff::find()
            ->alias('at')
            ->where([
                'at.client_account_id' => $account->id,
                'at.service_type_id' => ServiceType::ID_VOIP_PACKAGE_CALLS
            ])
            ->joinWith('accountTariffLogs.tariffPeriod.tariff t', false, 'INNER JOIN')
            ->select(['t.name', 't.id'])
            ->indexBy('id')
            ->orderBy(['t.name' => SORT_ASC])
            ->asArray()
            ->column();
    }

    /**
     * Получение регионов
     *
     * @param array $usageVoip
     * @return array
     */
    public function getRegions($usageVoip)
    {
        $regions = [];
        foreach ($usageVoip as $usage) {
            if (isset($regions[$usage['region']])) {
                continue;
            }

            $regions[$usage['region']] = $usage['region'];
        }

        return $regions;
    }

    /**
     * Список услуг телефонии и транки
     *
     * @param array $services
     * @param array $regions
     * @return array
     */
    public function prepareToSelect($services, $regions)
    {
        $select = [];

        if (count($regions) > 1) {
            $select[] = [
                'type' => 'usage',
                'is_all' => true,
            ];
        }

        $lastRegion = '';
        foreach ($services['voip'] as $usage) {
            if ($lastRegion != $usage['region']) {
                $select[] = [
                    'type' => 'usage',
                    'region' => $usage['region'],
                    'region_name' => $usage['region_name'],
                    'is_all' => 1,
                ];
                $lastRegion = $usage['region'];
            }

            $select[] = [
                'type' => 'usage',
                'region' => $usage['region'],
                'region_name' => $usage['region_name'],
                'is_all' => false,
                'id' => $usage['id'],
                'value' => $usage['phone_num'],
            ];
        }

        if ($services['trunk']) {
            $select[] = [
                'type' => 'trunk',
                'is_all' => true,
            ];
        }

        foreach ($services['trunk'] as $trunk) {
            $select[] = [
                'type' => 'trunk',
                'is_all' => false,
                'id' => $trunk['id'],
                'value' => $trunk['id'],
            ];
        }

        return $select;
    }

    /**
     * Конвертирует список услуг в данные для select'а
     *
     * @param array $usagesData
     * @param bool $isAsTemplate
     * @return string[]
     */
    public function usagesToSelect($usagesData, $isAsTemplate = false)
    {
        $convertData = $this->prepareToSelect($usagesData, $this->getRegions($usagesData['voip']));

        $tr = $isAsTemplate ?
            function ($str) {
                return "{" . $str . "}";
            } : // переводим в шаблоны и переводит ЛК
            function ($str) {
                return Yii::t('number', $str);
            }; // переводим внутри стата

        $select = [];

        foreach ($convertData as $type => $usage) {
            $key = $usage['type'] . '_' . (isset($usage['region']) ? $usage['region'] : '');

            ($usage['is_all'] || isset($usage['id'])) && $key .= '_' . ($usage['is_all'] ? 'all' : $usage['value']);

            if ($usage['type'] == 'usage') {
                if ($usage['is_all']) {
                    $value = isset($usage['region']) ? $usage['region_name'] . ' (' . $tr('All numbers') . ')' : $tr('All regions');
                } else {
                    $value = '&nbsp;&nbsp;' . $usage['value'];
                }
            } else { // trunk
                $value = ($usage['is_all'] ? $tr('All trunks') : $tr('Trunk') . ' #' . $usage['id']);
            }

            $select[$key] = $value;
        }

        return $select;
    }

    /**
     * Разбор полученного значения
     *
     * @param string $selected
     * @return array
     */
    public function decodeSelected($selected)
    {
        $e = explode('_', $selected);
        $type = $e[0];
        $region = $e[1];
        $value = $e[2];

        $data = ['type' => $type];

        if ($value == 'all') {
            return $data + [
                    'is_all' => true
                ] + ($region ? ['region' => $region] : []);
        }

        $data['is_all'] = false;
        $data['value'] = $value;

        return $data;
    }

    /**
     * Получаем настройки отчета
     *
     * @param string $selected
     * @param array $usagesData
     * @return array
     */
    public function reportConfig($selected, $usagesData)
    {
        $reportConfig = $this->decodeSelected($selected);

        $usageIds = [];
        $regions = [];
        $isTrunk = false;

        if ($reportConfig['type'] == 'usage') {
            foreach ($usagesData['voip'] as $usage) {
                if (
                    ($reportConfig['is_all'] &&
                        (isset($reportConfig['region']) ? $reportConfig['region'] == $usage['region'] : true)
                    ) ||
                    ($usage['phone_num'] == $reportConfig['value'])
                ) {
                    $usageIds[] = $usage['id'];
                    $regions[$usage['region']] = 1;
                }
            }
        } else { // trunk
            foreach ($usagesData['trunk'] as $trunk) {
                if ($reportConfig['is_all'] || $trunk['id'] == $reportConfig['value']) {
                    $usageIds[] = $trunk['id'];
                }

                $isTrunk = true;
            }
        }

        return [$usageIds, array_keys($regions), $isTrunk];
    }
}
