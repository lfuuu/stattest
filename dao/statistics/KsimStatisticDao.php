<?php

namespace app\dao\statistics;

use app\classes\helpers\ArrayHelper;
use app\classes\Singleton;
use app\models\billing\A2pSms;
use app\models\billing\CallsRaw;
use app\models\billing\DataRaw;
use app\models\billing\SmscRaw;
use app\models\EventQueue;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;

/**
 * @method static KsimStatisticDao me($args = null)
 */
class KsimStatisticDao extends Singleton
{
    /**
     * @param string $number
     * @return string
     */

    public function makeStatisticByNumber(?EventQueue $event, $number)
    {
        /** @var AccountTariff $at */
        $at = AccountTariff::find()->where(['voip_number' => $number])->andWhere(['not', ['tariff_period_id' => null]])->one();

        if (!$at) {
            throw new \InvalidArgumentException('not found atId for voip number: ' . $number);
        }

        $tzMoscow = new \DateTimeZone('Europe/Moscow');
        $tzUtc = new \DateTimeZone('UTC');

        $now = (new \DateTimeImmutable('now', $tzMoscow))->setTimezone($tzUtc);;

        $startDateStr = end($at->accountTariffLogs)->getAttributes(['actual_from_utc'])['actual_from_utc'];
        $startDate = (new \DateTimeImmutable($startDateStr, $tzUtc));

        $lastAtl = array_filter($at->accountTariffLogs, fn(AccountTariffLog $atl) => $atl->tariff_period_id == null);
        $endDate = $now;
        if ($lastAtl) {
            $endDateStr = reset($lastAtl)->getAttributes(['actual_from_utc'])['actual_from_utc'];
            $endDate = (new \DateTimeImmutable($endDateStr, $tzUtc));
        }

        $periodStart = (new \DateTimeImmutable('now', $tzMoscow))
            ->modify('- 31 day')
            ->setTime(0, 0, 0)->setTimezone($tzUtc);

        $periodStartStr = max($periodStart, $startDate)->format('Y-m-d H:i:s');
        $periodEndStr = min($now, $endDate)->format('Y-m-d H:i:s');


        $sql = <<<SQL
with a as (
    SELECT date_trunc('day', t.connect_time)  d,
           count(*) filter (where t.orig)     call_out,
           count(*) filter (where not t.orig) call_in
    FROM calls_raw.calls_raw t
    WHERE number_service_id = {$at->id}
      and connect_time > now() - interval '31 day'
    group by date_trunc('day', t.connect_time)
)
select g::date as date, coalesce(call_out, 0) call_out, coalesce(call_in, 0) call_in
from generate_series(date_trunc('day', now() - interval '31 day'), now(), interval '1 day') g
         left join a on a.d = g
SQL;

        $calls = CallsRaw::getDb()->createCommand($sql)->cache(120)->queryAll();

        $smsSql = <<<SQL
 with a as (
    SELECT date_trunc('day', t.setup_time + interval '3 hours') d,
           count(*) filter (where src_number = '{$number}') sms_out,
           count(*) filter (where dst_number = '{$number}') sms_in
    FROM smsc_raw.smsc_raw t
    WHERE '{$number}' in (src_number, dst_number)
      and setup_time between '{$periodStartStr}+00' and '{$periodEndStr}+00'
    group by d
)
select g::date as date, coalesce(sms_out, 0) sms_out, coalesce(sms_in, 0) sms_in
from generate_series(date_trunc('day', now() - interval '31 day'), now(), interval '1 day') g
         left join a on a.d = g;
SQL;

        $smss = SmscRaw::getDb()->createCommand($smsSql)->cache(120)->queryAll();

        $inetSql = <<<SQL
with a as (
    SELECT date_trunc('day', t.charge_time + interval '3 hours')                as d,
           ceil(sum(quantity) filter ( where service_id = 350 ) / 1024 / 1024)  as inet_in,
           ceil(sum(quantity) filter ( where service_id != 350 ) / 1024 / 1024) as inet_out
    FROM data_raw.data_raw t
    WHERE msisdn = '{$at->voip_number}'
      and number_service_id = {$at->id}
    and charge_time between '{$periodStartStr}+00' and '{$periodEndStr}+00'
    group by d
)
select g::date as date, coalesce(inet_in, 0) inet_in, coalesce(inet_out, 0) inet_out
from generate_series(date_trunc('day', now() - interval '31 day'), now(), interval '1 day') g
         left join a on a.d = g;
SQL;


        $inetData = DataRaw::getDb()->createCommand($inetSql)->cache(120)->queryAll();

        $data = ArrayHelper::merge(
            ArrayHelper::merge(
                ArrayHelper::index($calls, 'date'),
                ArrayHelper::index($smss, 'date')
            ),
            ArrayHelper::index($inetData, 'date')
        );

        $result = '';
        foreach ($data as $day => $d) {
            $result .= implode(';', [
                        $at->voip_number,
                        $day,
                        $d['sms_in'],
                        $d['sms_out'],
                        0, 0,
                        $d['call_in'],
                        $d['call_out'],
                        0, 0,
                        $d['inet_out'],
                        $d['inet_in'],
                        0, 0,
                    ]
                ) . PHP_EOL;
        }

        $event->log_error = $result;

        return $result;
    }
}
