<?php

namespace app\dao\reports;

use Yii;
use DateTime;
use DateTimeZone;
use yii\db\ActiveQuery;
use yii\db\Expression;
use app\classes\Singleton;
use app\models\ClientAccount;
use app\models\billing\Calls;
use app\models\billing\Geo;
use app\models\UsageVoipPackage;

class ReportUsageDao extends Singleton
{

    public static function getUsageVoipStatistic(
        $region, $from, $to, $detality, $client_id, $usage_arr = [],
        $paidonly = 0, $destination = 'all', $direction = 'both', $timezone = 'Europe/Moscow', $is_full = false,
        $packages = []
    )
    {
        if (!$timezone) {
            $timezone = 'Europe/Moscow';
        }

        if (!($timezone instanceof DateTimeZone)) {
            $timezone = new DateTimeZone($timezone);
        }

        $from = (new DateTime(date('Y-m-d', $from), $timezone))->setTime(0, 0, 0);
        $to = (new DateTime(date('Y-m-d', $to), $timezone))->setTime(23, 59, 59);

        $from->setTimezone(new DateTimeZone('UTC'));
        $to->setTimezone(new DateTimeZone('UTC'));

        $clientAccount = ClientAccount::findOne($client_id);
        $query = Calls::find();

        $query->andWhere(['>=', 'connect_time', $from->format('Y-m-d H:i:s')]);
        $query->andWhere(['<=', 'connect_time', $to->format('Y-m-d H:i:s.999999')]);

        if ($direction != 'both') {
            $query->andWhere(['orig' => ($direction == 'in' ? 'false' : 'true')]);
        }

        if (isset($usage_arr) && count($usage_arr) > 0) {
            $query->andWhere(['in', 'number_service_id', $usage_arr]);
        }

        if ($paidonly) {
            $query->andWhere('ABS(cost) > 0.0001');
        }

        if ($destination != 'all') {
            list ($dest, $mobile) = explode('-', $destination);

            if ((int) $dest == 0) {
                $query->andWhere(['<=', 'destination_id', (int) $dest]);
            }
            else {
                $query->andWhere(['destination_id' => (int) $dest]);
            }

            if ($mobile) {
                if ($mobile == 'm') {
                    $query->andWhere('mob = true');
                }
                else if ($mobile == 'f') {
                    $query->andWhere('mob = false');
                }
            }
        }

        switch ($detality) {
            case 'dest':
                return self::voipStatisticByDestination($query, $clientAccount, $region);
                break;
            default:
                return self::voipStatistic($query, $clientAccount, $from, $packages, $detality, $paidonly, $is_full);
                break;
        }
    }

    public static function getUsageVoipPackagesStatistic($usage_id, $package_id = 0, $date_range_from = '', $date_range_to = '')
    {
        $packages = UsageVoipPackage::find()->where(['usage_voip_id' => $usage_id]);

        if ((int) $package_id) {
            $packages->andWhere(['id' => $package_id]);
        }

        if ($date_range_from instanceof DateTime) {
            $packages->andWhere(['<', 'actual_from', $date_range_from->format('Y-m-d H:i:s')]);
        }

        if ($date_range_to instanceof DateTime) {
            $packages->andWhere(['>', 'actual_to', $date_range_to->format('Y-m-d H:i:s')]);
        }

        return $packages->all();
    }

    private static function voipStatistic(ActiveQuery $query, ClientAccount $clientAccount, DateTime $from, $packages = [], $detality, $paid_only, $is_full)
    {
        $offset = $from->getOffset();
        $format = 'd месяца Y г. H:i:s';
        $groupBy = '';

        switch ($detality) {
            case 'call': {
                $format = 'd месяца Y г. H:i:s';
                break;
            }
            case 'day': {
                $groupBy = "date_trunc('day', connect_time + '" . $offset . " second'::interval)";
                $format = 'd месяца Y г.';
                break;
            }
            case 'year': {
                $groupBy = "date_trunc('year', connect_time + '" . $offset . " second'::interval)";
                $format = 'Y г.';
                break;
            }
            case 'month': {
                $groupBy = "date_trunc('month', connect_time + '" . $offset . " second'::interval)";
                $format = 'Месяц Y г.';
                break;
            }
        }

        if (isset($packages) && count($packages) > 0) {
            $query->andWhere(['in', 'service_package_id', $packages]);
        }

        if ($query->count() >= 5000) {
            throw new \Exception('Статистика отображается не полностью. Сделайте ее менее детальной или сузьте временной период');
        }

        if ($groupBy) {
            $query->groupBy([$groupBy]);
        }

        if (!$groupBy) {
            $query->select([
                'id',
                'src_number',
                'geo_id',
                'geo_mob',
                'dst_number',
                'orig',
            ]);
        }

        switch ($detality) {
            case 'day':
                $query->addSelect([
                    'ts1' => "date_trunc('day', connect_time + '" . $offset . " second'::interval)",
                ]);
                break;
            case 'month':
                $query->addSelect([
                    'ts1' => "date_trunc('month', connect_time + '" . $offset . " second'::interval)",
                ]);
                break;
            case 'year':
                $query->addSelect([
                    'ts1' => "date_trunc('year', connect_time + '" . $offset . " second'::interval)",
                ]);
                break;
            default:
                $query->addSelect([
                    'ts1' => new Expression("connect_time + '" . $offset . " second'::interval"),
                ]);
        }

        $query->addSelect([
            'price' => ($groupBy ? '-sum' : '-') . '(cost)',
            'ts2' => ($groupBy ? 'sum' : '') . '(' . ($paid_only ? 'case abs(cost)>0.0001 when true then billed_time else 0 end' : 'billed_time') . ')',
            'cnt' => $groupBy ? 'sum(' . ($paid_only ? 'case abs(cost) > 0.0001 when true then 1 else 0 end' : new Expression('1')) .')' : new Expression('1'),
        ]);

        $query->orderBy('ts1 ASC');
        $query->limit($is_full ? 50000 : 5000);

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

            $dt = explode(' ', $record['ts1']);
            $d = explode('-', $dt[0]);
            if (count($dt) > 1) {
                $t = explode(':', $dt[1]);
            }
            else {
                $t = [0, 0, 0];
            }

            $ts =
                (new DateTime)
                    ->setDate($d[0], $d[1], $d[2])
                    ->setTime($t[0], $t[1], (int) $t[2]);

            $record['tsf1'] = $ts->format('Y-m-d H:i:s');
            $record['mktime'] = $ts;
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

        $tax_rate = $clientAccount->getTaxRate();
        if ($clientAccount->price_include_vat) {
            $rt['price_without_tax'] = number_format($rt['price'] * 100 / (100 + $tax_rate), 2, '.', '');
            $rt['price_with_tax'] = number_format($rt['price'], 2, '.', '');
            $rt['price'] = $rt['price_with_tax'] . ' (включая НДС)';
        }
        else {
            $rt['price_without_tax'] = number_format($rt['price'], 2, '.', '');
            $rt['price_with_tax'] = number_format($rt['price'] * (100 + $tax_rate) / 100, 2, '.', '');
            $rt['price'] = $rt['price_without_tax'] . ' (<b>' . $rt['price_with_tax'] . ' - Сумма с НДС</b>)';
        }

        $result['total'] = $rt;

        return $result;
    }

    private static function voipStatisticByDestination(ActiveQuery $query, ClientAccount $clientAccount, $region)
    {
        $query->select([
            'dest' => 'destination_id',
            'mob',
            'price' => '-sum(cost)',
            'len' => 'sum(billed_time)',
            'cnt' => 'sum(1)'
        ]);

        $query->andWhere(['server_id' => (int) $region]);
        $query->groupBy(['destination_id', 'mob']);

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

        $tax_rate = $clientAccount->getTaxRate();
        $delta = 0;
        $total_row = [
            'is_total' => true,
            'tsf1' => 'Итого'
        ];

        if ($len >= 24 * 60 * 60){
            $delta = floor($len / (24 * 60 * 60));
        }

        $total_row['tsf2'] = ($delta ? $delta . 'd ' : '') . gmdate('H:i:s', $len - $delta * 24 * 60 * 60);

        if ($clientAccount->price_include_vat) {
            $total_row['price_without_tax'] = number_format($price * 100 / (100 + $tax_rate), 2, '.', '');
            $total_row['price_with_tax'] = number_format($price, 2, '.', '');
            $total_row['price'] = $total_row['price_with_tax'] . ' (включая НДС)';
        }
        else {
            $total_row['price_without_tax'] = number_format($price, 2, '.', '');
            $total_row['price_with_tax'] = number_format($price * (100 + $tax_rate) / 100, 2, '.', '');
            $total_row['price'] = $total_row['price_without_tax'] . ' (<b>' . $total_row['price_with_tax'] . ' - Сумма с НДС</b>)';
        }

        $total_row['cnt'] = $cnt;
        $result['total'] = $total_row;

        return $result;
    }

}