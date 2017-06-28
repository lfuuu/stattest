<?php

namespace app\dao\reports;

use app\models\UsageTrunk;
use app\models\UsageVoip;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use Yii;
use DateTime;
use DateTimeZone;
use yii\db\ActiveQuery;
use yii\db\Expression;
use app\classes\Singleton;
use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\billing\Calls;
use app\models\billing\Geo;
use app\models\billing\InstanceSettings;
use app\models\UsageVoipPackage;
use app\modules\nnp\models\PackageMinute;
use app\modules\nnp\models\PackagePrice;
use app\modules\nnp\models\PackagePricelist;

class ReportUsageDao extends Singleton
{

    const REPORT_MAX_ITEMS = 50000;
    const REPORT_MAX_VIEW_ITEMS = 5000;

    /**
     * Статистика по телефонии
     *
     * @param string $region
     * @param string $from
     * @param string $to
     * @param string $detality
     * @param int $clientId
     * @param array $usages
     * @param int $paidonly
     * @param string $destination
     * @param string $direction
     * @param bool|false $isFull
     * @param array $packages
     * @return array
     * @throws \Exception
     */
    public function getUsageVoipStatistic(
        $region,
        $from,
        $to,
        $detality,
        $clientId,
        $usages = [],
        $paidonly = 0,
        $destination = 'all',
        $direction = 'both',
        $isFull = false,
        $packages = []
    ) {
        $from = (new DateTime('now', new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT)))
            ->setTimestamp($from)
            ->setTime(0, 0, 0);

        $to = (new DateTime('now', new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT)))
            ->setTimestamp($to)
            ->setTime(23, 59, 59);

        $clientAccount = ClientAccount::findOne($clientId);
        $query = Calls::find()
            ->alias('cr')
            ->andWhere([
                'BETWEEN',
                'cr.connect_time',
                $from->format(DateTimeZoneHelper::DATETIME_FORMAT),
                $to->format(DateTimeZoneHelper::DATETIME_FORMAT . '.999999')
            ])
            ->andWhere(['account_id' => $clientId]);

        $direction !== 'both' && $query->andWhere(['cr.orig' => ($direction === 'in' ? 'false' : 'true')]);
        isset($usages) && count($usages) > 0 && $query->andWhere([($region == 'trunk' ? 'trunk_service_id' : 'number_service_id') => $usages]);
        $paidonly && $query->andWhere('ABS(cr.cost) > 0.0001');

        $region == 'trunk' && $query->andWhere(['number_service_id' => null]); // статистика по транкам - смотрится по транкам. Звонки по услугам могут быть привязаны к мультитранкам.

        if ($destination !== 'all') {
            list ($dest, $mobile, $zone) = explode('-', $destination);

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
            case 'dest':
                return $this->_voipStatisticByDestination($query, $clientAccount);
                break;
            default:
                return $this->_voipStatistic($query, $clientAccount, $from, $packages, $detality, $paidonly, $isFull);
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
     * @param ClientAccount $clientAccount
     * @param DateTime $from
     * @param array $packages
     * @param string $detality
     * @param int $paidOnly
     * @param boolean $isFull
     * @return array
     * @throws \Exception
     */
    private function _voipStatistic(
        ActiveQuery $query,
        ClientAccount $clientAccount,
        DateTime $from,
        $packages = [],
        $detality = '',
        $paidOnly = 0,
        $isFull = false
    ) {
        $offset = $from->getOffset();

        if (isset($packages) && count($packages) > 0) {
            $query->andWhere(['in', 'cr.service_package_id', $packages]);
        }

        switch ($detality) {
            case 'day':
            case 'year':
            case 'month': {
                $groupBy = new Expression("DATE_TRUNC('" . $detality . "', cr.connect_time + '" . $offset . " second'::interval)");
                $query->addSelect([
                    'ts1' => new Expression("DATE_TRUNC('" . $detality . "', cr.connect_time + '" . $offset . " second'::interval)"),
                ]);
                break;
            }
            default: {
                $groupBy = '';
                $query->addSelect([
                    'ts1' => new Expression("cr.connect_time + '" . $offset . " second'::interval"),
                ]);
                break;
            }
        }

        if ($groupBy) {
            $query->groupBy([$groupBy]);
        }

        if (!$groupBy) {
            $query->addSelect([
                'cr.id',
                'cr.src_number',
                'cr.geo_id',
                'cr.geo_mob',
                'cr.dst_number',
                'cr.orig',
            ]);
        }

        $query->addSelect([
            'price' => ($groupBy ? '-SUM' : '-') . '(cr.cost)',
            'ts2' => ($groupBy ? 'SUM' : '') . '(' . ($paidOnly ? 'CASE ABS(cr.cost) > 0.0001 WHEN true THEN cr.billed_time ELSE 0 END' : 'cr.billed_time') . ')',
            'cnt' => $groupBy ? 'SUM(' . ($paidOnly ? 'CASE ABS(cr.cost) > 0.0001 WHEN true THEN 1 ELSE 0 END' : new Expression('1')) . ')' : new Expression('1'),
        ]);

        $isWithPackageDetail = false;
        if (!$groupBy && $clientAccount->account_version == ClientAccount::VERSION_BILLER_UNIVERSAL) {
            $isWithPackageDetail = true;

            $query->addSelect([
                'nnp_package_minute_id',
                'nnp_package_price_id',
                'nnp_package_pricelist_id',
                'package_time',
                'billed_time',
            ]);
        }

        if ($query->count() >= self::REPORT_MAX_VIEW_ITEMS) {
            Yii::$app->session->setFlash('error',
                'Статистика отображается не полностью.' .
                Html::tag('br') . PHP_EOL .
                ' Сделайте ее менее детальной или сузьте временной период'
            );
        }

        $query->limit($isFull ? self::REPORT_MAX_ITEMS : self::REPORT_MAX_VIEW_ITEMS);
        $query->orderBy('ts1 ASC');

        $rt = ['price' => 0, 'ts2' => 0, 'cnt' => 0, 'is_total' => true];
        $geo = [];

        foreach ($query->asArray()->each() as $record) {
            $record['geo'] = '';

            if (isset($record['geo_id'])) {
                if (!isset($geo[$record['geo_id']])) {
                    $geo[$record['geo_id']] = Geo::find()
                        ->select('name')
                        ->where(['id' => (int)$record['geo_id']])
                        ->scalar();
                }

                $record['geo'] = $geo[$record['geo_id']];
                if ($record['geo_mob'] === true) {
                    $record['geo'] .= ' (mob)';
                }
            }

            $ts = new DateTime($record['ts1']);

            $record['tsf1'] = $ts->format(DateTimeZoneHelper::DATETIME_FORMAT);
            $record['mktime'] = $ts->getTimestamp();
            $record['is_total'] = false;

            if ($record['ts2'] >= 24 * 60 * 60) {
                $d = floor($record['ts2'] / (24 * 60 * 60));
            } else {
                $d = 0;
            }

            $record['tsf2'] = ($d ? $d . 'd ' : '') . gmdate('H:i:s', $record['ts2']);
            $record['price'] = number_format($record['price'], 2, '.', '');

            if ($isWithPackageDetail) {
                $this->_admixedPackageDetails($record);
            }

            $result[] = $record;
            $rt['price'] += $record['price'];
            $rt['cnt'] += $record['cnt'];
            $rt['ts2'] += $record['ts2'];
        }

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
        $rt = self::_getTotalPrices($clientAccount, $rt);

        $result['total'] = $rt;

        return $result;
    }

    /**
     * Вспомогательная функция. Статистика по направлениям
     *
     * @param ActiveQuery $query
     * @param ClientAccount $clientAccount
     * @return array
     */
    private function _voipStatisticByDestination(ActiveQuery $query, ClientAccount $clientAccount)
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

        foreach ($query->asArray()->each() as $record) {
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
        }

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
            'tsf1' => 'Итого'
        ];

        if ($len >= 24 * 60 * 60) {
            $delta = floor($len / (24 * 60 * 60));
        }

        $total_row['tsf2'] = ($delta ? $delta . 'd ' : '') . gmdate('H:i:s', $len - $delta * 24 * 60 * 60);
        $total_row = self::_getTotalPrices($clientAccount, $total_row);
        $total_row['cnt'] = $cnt;

        $result['total'] = $total_row;

        return $result;
    }

    /**
     * Формирование итоговых значений
     *
     * @param ClientAccount $clientAccount
     * @param array $row
     * @return array
     */
    private static function _getTotalPrices(ClientAccount $clientAccount, array $row = [])
    {
        $taxRate = $clientAccount->getTaxRate();

        if ($clientAccount->price_include_vat) {
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

    /**
     * Добавляет к строке детализации звонка данные по использованным пакетам
     * 
     * @param array $record
     */
    private function _admixedPackageDetails(&$record)
    {
        // Детализация универсальных пакетов.
        $packageMinute = $record['nnp_package_minute_id'] ?
            PackageMinute::findOne([
                'id' => $record['nnp_package_minute_id']
            ]) : null;

        /** @var PackageMinute $packageMinute */
        if ($packageMinute) {
            $record['package_minute'] = [
                'name' => $packageMinute->tariff->name,
                'minute' => $packageMinute->minute,
                'destination' => $packageMinute->destination->name,
                'taken' => 'none',
            ];
        }

        $packagePrice = $record['nnp_package_price_id'] ?
            PackagePrice::findOne([
                'id' => $record['nnp_package_price_id']
            ]) : null;

        /** @var PackagePrice $packagePrice */
        if ($packagePrice) {
            $record['package_price'] = [
                'name' => $packagePrice->tariff->name,
                'price' => $packagePrice->price,
                'destination' => $packagePrice->destination->name,
                'taken' => 'none',
            ];
        }

        $packagePriceList = $record['nnp_package_pricelist_id'] ?
            PackagePricelist::findOne([
                'id' => $record['nnp_package_pricelist_id']
            ]) : null;

        if ($packagePriceList) {
            $record['package_pricelist'] = [
                'name' => $packagePriceList->tariff->name,
                'pricelist' => $packagePriceList->pricelist->name,
                'taken' => 'none',
            ];
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
                if ($record['package_price']) {
                    $record['package_price']['taken'] = 'all';
                } elseif ($record['package_pricelist']) {
                    $record['package_pricelist']['taken'] = 'all';
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
            $record['billed_time']
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
                ->with('city', 'region')
                ->orderBy([
                    AccountTariff::tableName() . '.id' => SORT_ASC
                ]);

            $usages = [];

            /** @var AccountTariff $accountTariff */
            foreach ($accountTariffs->each() as $accountTariff) {
                $region = $accountTariff->city->region;
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
     * @return string[]
     */
    public function usagesToSelect($usagesData)
    {
        $convertData = self::me()->prepareToSelect($usagesData, $this->getRegions($usagesData['voip']));

        $select = [];

        foreach ($convertData as $type => $usage) {
            $key = $usage['type'] . '_' . (isset($usage['region']) ? $usage['region'] : '');

            ($usage['is_all'] || isset($usage['id'])) && $key .= '_' . ($usage['is_all'] ? 'all' : $usage['value']);

            if ($usage['type'] == 'usage') {
                if ($usage['is_all']) {
                    $value = isset($usage['region']) ? $usage['region_name'] . ' (все номера)' : 'Все регионы';
                } else {
                    $value = '&nbsp;&nbsp;' . $usage['value'];
                }
            } else { // trunk
                $value = ($usage['is_all'] ? 'Все транки' : 'Транк #' . $usage['id']);
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
        list($type, $region, $value) = $e;

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