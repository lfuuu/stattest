<?php

namespace app\dao\reports;

use Yii;
use DateTime;
use DateTimeZone;
use yii\db\ActiveQuery;
use yii\db\Expression;
use app\classes\Singleton;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\billing\Calls;
use app\models\billing\Geo;
use app\models\billing\InstanceSettings;
use app\models\UsageVoipPackage;

class ReportUsageDao extends Singleton
{

    const REPORT_MAX_ITEMS = 50000;
    const REPORT_MAX_VIEW_ITEMS = 5000;

    /**
     * @param string $region
     * @param string $from
     * @param string $to
     * @param string $detality
     * @param int $clientId
     * @param array $usages
     * @param int $paidonly
     * @param string $destination
     * @param string $direction
     * @param string $timezone
     * @param bool|false $isFull
     * @param array $packages
     * @return array
     * @throws \Exception
     */
    public static function getUsageVoipStatistic(
        $region, $from, $to, $detality, $clientId, $usages = [],
        $paidonly = 0, $destination = 'all', $direction = 'both', $timezone = DateTimeZoneHelper::TIMEZONE_MOSCOW, $isFull = false,
        $packages = []
    )
    {
        if (!$timezone) {
            $timezone = DateTimeZoneHelper::TIMEZONE_MOSCOW;
        }

        if (!($timezone instanceof DateTimeZone)) {
            $timezone = new DateTimeZone($timezone);
        }

        $from =
            (new DateTime('now', $timezone))
                ->setTimestamp($from)
                ->setTimezone(new DateTimeZone('UTC'))
                ->setTime(0, 0, 0);
        $to =
            (new DateTime('now', $timezone))
                ->setTimestamp($to)
                ->setTimezone(new DateTimeZone('UTC'))
                ->setTime(23, 59, 59);

        $clientAccount = ClientAccount::findOne($clientId);
        $query =
            Calls::find()
                ->from(['cr' => Calls::tableName()]);

        $query->andWhere(['between', 'cr.connect_time', $from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s.999999')]);

        if ($direction != 'both') {
            $query->andWhere(['cr.orig' => ($direction == 'in' ? 'false' : 'true')]);
        }

        if (isset($usages) && count($usages) > 0) {
            $query->andWhere(['in', 'cr.number_service_id', $usages]);
        }

        if ($paidonly) {
            $query->andWhere('ABS(cr.cost) > 0.0001');
        }

        if ($destination != 'all') {
            list ($dest, $mobile, $zone) = explode('-', $destination);

            if ((int) $dest == 0) {
                $query->andWhere(['<=', 'cr.destination_id', (int) $dest]);
            }
            else {
                $query->andWhere(['cr.destination_id' => (int) $dest]);
            }

            switch ($mobile) {
                case 'm':
                    $query->andWhere('cr.mob');
                    break;
                case 'f':
                    $query->andWhere('NOT cr.mob');
                    break;
            }

            if ((int) $dest == 0 && $mobile === 'f') {
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
                return self::voipStatisticByDestination($query, $clientAccount, $region);
                break;
            default:
                return self::voipStatistic($query, $clientAccount, $from, $packages, $detality, $paidonly, $isFull);
                break;
        }
    }

    /**
     * @param int $usageId
     * @param int $packageId
     * @param string $dateRangeFrom
     * @param string $dateRangeTo
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getUsageVoipPackagesStatistic($usageId, $packageId = 0, $dateRangeFrom = '', $dateRangeTo = '')
    {
        $packages = UsageVoipPackage::find()->where(['usage_voip_id' => $usageId]);

        if ((int) $packageId) {
            $packages->andWhere(['id' => $packageId]);
        }

        if ($dateRangeFrom instanceof DateTime) {
            $packages->andWhere(['>=', 'actual_from', new Expression('CAST(:dateRangeFrom AS DATE)')]);
        }

        if ($dateRangeTo instanceof DateTime) {
            $packages->andWhere([
                'or',
                ['<=', 'actual_to', new Expression('CAST(:dateRangeTo AS DATE)')],
                ['>', 'actual_to', new Expression('CAST(:dateRangeTo AS DATE)')]
            ]);
        }

        $packages->addParams([
            ':dateRangeFrom' => $dateRangeFrom->format(DateTime::ATOM),
            ':dateRangeTo' => $dateRangeTo->format(DateTime::ATOM),
        ]);

        return $packages->all();
    }

    /**
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
    private static function voipStatistic(ActiveQuery $query, ClientAccount $clientAccount, DateTime $from, $packages = [], $detality, $paidOnly = 0, $isFull = false)
    {
        $offset = $from->getOffset();

        if (isset($packages) && count($packages) > 0) {
            $query->andWhere(['in', 'cr.service_package_id', $packages]);
        }

        if ($query->count() >= self::REPORT_MAX_VIEW_ITEMS) {
            throw new \InvalidArgumentException('Статистика отображается не полностью. Сделайте ее менее детальной или сузьте временной период');
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
            'cnt' => $groupBy ? 'SUM(' . ($paidOnly ? 'CASE ABS(cr.cost) > 0.0001 WHEN true THEN 1 ELSE 0 END' : new Expression('1')) .')' : new Expression('1'),
        ]);

        $query->orderBy('ts1 ASC');
        $query->limit($isFull ? self::REPORT_MAX_ITEMS : self::REPORT_MAX_VIEW_ITEMS);

        $records = $query->asArray()->all();

        $rt = ['price' => 0, 'ts2' => 0, 'cnt' => 0, 'is_total' => true];
        $geo = [];

        foreach ($records as $record) {
            $record['geo'] = '';

            if (isset($record['geo_id'])) {
                if (!isset($geo[$record['geo_id']])) {
                    $geo[$record['geo_id']] = Geo::find()->select('name')->where(['id' => (int) $record['geo_id']])->scalar();
                }
                $record['geo'] = $geo[$record['geo_id']];
                if ($record['geo_mob'] === true) {
                    $record['geo'] .= ' (mob)';
                }
            }

            $record['tsf1'] = Yii::$app->formatter->asDatetime(
                (new DateTime($record['ts1']))
                    ->setTimezone(new DateTimeZone(DateTimeZoneHelper::getUserTimeZone()))
            );
            $record['mktime'] = $record['tsf1'];
            $record['is_total'] = false;

            if ($record['ts2'] >= 24 * 60 * 60) {
                $d = floor($record['ts2'] / (24 * 60 * 60));
            }
            else {
                $d = 0;
            }

            $record['tsf2'] = ($d ? $d . 'd ' : '') . gmdate('H:i:s', $record['ts2']);
            $record['price'] = number_format($record['price'], 2, '.', '');

            $result[] = $record;
            $rt['price'] += $record['price'];
            $rt['cnt'] += $record['cnt'];
            $rt['ts2'] += $record['ts2'];
        }

        $rt['ts1'] = 'Итого';
        $rt['tsf1'] = 'Итого';
        $rt['num_to'] = '&nbsp;';
        $rt['num_from'] = '&nbsp;';

        if ($rt['ts2'] >= 24 * 60 * 60) {
            $d = floor($rt['ts2'] / (24 *60 *60));
        }
        else {
            $d = 0;
        }

        $rt['tsf2'] = ($d ? $d . 'd ' : '') . gmdate('H:i:s', $rt['ts2'] - $d * 24 * 60 * 60);
        $rt = self::getTotalPrices($clientAccount, $rt);

        $result['total'] = $rt;

        return $result;
    }

    /**
     * @param ActiveQuery $query
     * @param ClientAccount $clientAccount
     * @param int $region
     * @return array
     */
    private static function voipStatisticByDestination(ActiveQuery $query, ClientAccount $clientAccount, $region)
    {
        $query->select([
            'dest' => 'cr.destination_id',
            'cr.mob',
            'price' => '-SUM(cr.cost)',
            'len' => 'SUM(cr.billed_time)',
            'cnt' => 'SUM(1)'
        ]);

        $query->andWhere(['cr.server_id' => (int) $region]);
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

        foreach ($query->asArray()->all() as $record) {
            if ($record['dest'] <= 0 && $record['mob'] === false) {
                $result['mos_loc']['len'] += $record['len'];
                $result['mos_loc']['price'] += $record['price'];
                $result['mos_loc']['cnt'] += $record['cnt'];
            }
            elseif ($record['dest'] <= 0 && $record['mob'] === true) {
                $result['mos_mob']['len'] += $record['len'];
                $result['mos_mob']['price'] += $record['price'];
                $result['mos_mob']['cnt'] += $record['cnt'];
            }
            elseif ($record['dest'] == 1 && $record['mob'] === false) {
                $result['rus_fix']['len'] += $record['len'];
                $result['rus_fix']['price'] += $record['price'];
                $result['rus_fix']['cnt'] += $record['cnt'];
            }
            elseif ($record['dest'] == 1 && $record['mob'] === true){
                $result['rus_mob']['len'] += $record['len'];
                $result['rus_mob']['price'] += $record['price'];
                $result['rus_mob']['cnt'] += $record['cnt'];
            }
            elseif ($record['dest'] == 2 || $record['dest'] == 3){
                $result['int']['len'] += $record['len'];
                $result['int']['price'] += $record['price'];
                $result['int']['cnt'] += $record['cnt'];
            }
        }

        $cnt = 0; $len = 0; $price = 0;
        foreach ($result as $destination => $data) {
            $cnt += $data['cnt'];
            $len += $data['len'];
            $price += $data['price'];

            $delta = 0;
            if ($data['len'] >= 24 * 60 * 60) {
                $delta = floor($data['len'] / (24 * 60 * 60));
            }

            $result[$destination]['tsf2'] = ($delta ? $delta . 'd ' : '') . gmdate('H:i:s', $data['len'] - $delta * 24 * 60 * 60);
            $result[$destination]['price'] = number_format($data['price'], 2, '.','');
        }

        $delta = 0;
        $total_row = [
            'is_total' => true,
            'tsf1' => 'Итого'
        ];

        if ($len >= 24 * 60 * 60){
            $delta = floor($len / (24 * 60 * 60));
        }

        $total_row['tsf2'] = ($delta ? $delta . 'd ' : '') . gmdate('H:i:s', $len - $delta * 24 * 60 * 60);
        $total_row = self::getTotalPrices($clientAccount, $total_row);
        $total_row['cnt'] = $cnt;

        $result['total'] = $total_row;

        return $result;
    }

    /**
     * @param ClientAccount $clientAccount
     * @param array $row
     * @return array
     */
    private static function getTotalPrices(ClientAccount $clientAccount, $row = [])
    {
        $taxRate = $clientAccount->getTaxRate();

        if ($clientAccount->price_include_vat) {
            $row['price_without_tax'] = number_format($row['price'] * 100 / (100 + $taxRate), 2, '.', '');
            $row['price_with_tax'] = number_format($row['price'], 2, '.', '');
            $row['price'] = $row['price_with_tax'] . ' (включая НДС)';
        }
        else {
            $row['price_without_tax'] = number_format($row['price'], 2, '.', '');
            $row['price_with_tax'] = number_format($row['price'] * (100 + $taxRate) / 100, 2, '.', '');
            $row['price'] = $row['price_without_tax'] . ' (<b>' . $row['price_with_tax'] . ' - Сумма с НДС</b>)';
        }

        return $row;
    }

}