<?php

namespace app\classes\voip;


use app\classes\helpers\ArrayHelper;

class EmptyNumberFiller
{
    public function get()
    {
        $usages = $this->getUsages();

        $phoneNumbering = $this->getPhoneNumbering();

        $defaultStart = '2017-12-01 00:00:00';
        $data = [];
        foreach ($phoneNumbering as $range) {
            $statNumbers = $this->loadFromStat($range);

            for ($phone = $range['from']; $phone <= $range['to']; $phone++) {

//                if ($phone != 74997060065) {
//                    continue;
//                }

                $date = array_key_exists($phone, $statNumbers) ? ($statNumbers[$phone] ?: $defaultStart) : $defaultStart;

                if ($date < $defaultStart) {
                    $date = $defaultStart;
                }

                if (!array_key_exists($phone, $usages)) {
                    $data[] = [
                        'client_id' => 47197,
                        'number' => $phone,
                        'activation_dt' => $date,
                        'expire_dt' => '',
                        'type' => 'start',
                    ];
                    continue;
                }

                $numberUsages = $this->fm($usages[$phone]);

                $first = reset($usages[$phone]);
                if ($first['activation_dt'] > $date) {
                    $data[] = [
                        'client_id' => 47197,
                        'number' => $phone,
                        'activation_dt' => $date,
                        'expire_dt' => (new \DateTimeImmutable($first['activation_dt']))->modify('-1 second')->format('Y-m-d H:i:s'),
                        'type' => 'first',
                    ];
                }

                $data = array_merge($data, $numberUsages);

                $last = end($usages[$phone]);

                if ($last['expire_dt']) {
                    $data[] = [
                        'client_id' => 47197,
                        'number' => $phone,
                        'activation_dt' => (new \DateTimeImmutable($last['expire_dt']))->modify('+1 second')->format('Y-m-d H:i:s'),
                        'expire_dt' => '',
                        'type' => 'end',
                    ];
                }
            }
        }

        return $data;
    }

    private function fm($numberUsages)
    {
        $data = [];
        foreach ($numberUsages as $idx => $usage) {
//            $data[] = $usage;

            if (isset($numberUsages[$idx+1])) {
                $dtExp1 = new \DateTimeImmutable($usage['expire_dt']);
                $dtAct2 = new \DateTimeImmutable($numberUsages[$idx+1]['activation_dt']);

                if ($dtExp1->modify('+2 second') < $dtAct2) {
                    $data[] = [
                        'client_id' => 47197,
                        'number' => $usage['number'],
                        'activation_dt' => ($dtExp1)->modify('+1 second')->format('Y-m-d H:i:s'),
                        'expire_dt' => ($dtAct2)->modify('-1 second')->format('Y-m-d H:i:s'),
                        'type' => 'middle',
                    ];
                }
            }
        }

        return $data;
    }

    private function getPhoneNumbering()
    {
        return \Yii::$app->dbPg
            ->createCommand('select number_from as from, number_to as to from sorm_itgrad.phone_numbering where region_id = :region order by number_from', [':region' => 99])
            ->queryAll();
    }

    private function loadFromStat($range)
    {
        $numbers = \Yii::$app->db->createCommand('select number, date_start from voip_numbers where region = :region and number between :from and :to', [
            ':region' => 99,
            ':from' => $range['from'],
            ':to' => $range['to']
        ])->queryAll();

        $data = [];
        foreach($numbers as $row) {
            $data[$row['number']] = $row['date_start'];
        }

        return $data;
    }

    private function getPhoneSqlNotFiltred()
    {
        $sql = <<<SQL
SELECT client_id,
       number,
       if(activation_dt < '2017-12-01 00:00:00', '2017-12-01 00:00:00', activation_dt) as activation_dt,
       IF(expire_dt > '3000-01-01 00:00:00', NULL, expire_dt)                          AS expire_dt
FROM (
         SELECT c.id AS client_id,
                u.e164 as number,
                activation_dt,
                expire_dt
         FROM usage_voip u,
              voip_numbers v,
              clients c
         WHERE c.client = u.client
           AND v.region = '99'
           AND u.e164 = v.number
           AND u.expire_dt >= '2017-12-01 00:00:00'

         UNION

         SELECT client_account_id AS client_id,
                voip_number       AS number,
                (SELECT actual_from_utc + INTERVAL 3 HOUR
                 FROM uu_account_tariff_log
                 WHERE account_tariff_id = u.id
                   AND tariff_period_id IS NOT NULL
                 ORDER BY actual_from_utc
                 LIMIT 1)            activation_dt,
                (SELECT actual_from_utc + INTERVAL 3 HOUR
                 FROM uu_account_tariff_log
                 WHERE account_tariff_id = u.id
                   AND tariff_period_id IS NULL
                 ORDER BY actual_from_utc DESC
                 LIMIT 1)            expire_dt
         FROM uu_account_tariff u,
              voip_numbers v,
              clients c
         WHERE v.region = '99'
           AND u.voip_number = v.number
           AND c.id = u.client_account_id
           AND service_type_id = 2
         HAVING expire_dt IS NULL
             OR expire_dt > '2017-12-01 00:00:00'
     ) a
SQL;

        return $sql;

    }

    private function getPhonesSql()
    {
        $notFiltred = $this->getPhoneSqlNotFiltred();

        $sql = <<<SQL
SELECT client_id,
       a.number,
       activation_dt,
       expire_dt
FROM ({$notFiltred}) a,
     clients c,
     client_contract cc
         left join (
         SELECT contract_id
         FROM client_document
         WHERE type = 'contract'
         group by contract_id
     ) cd on cd.contract_id = cc.id
WHERE a.client_id = c.id
  AND cc.id = c.contract_id
  AND cc.state != 'unchecked'
  AND c.voip_credit_limit_day > 0
  AND cc.business_process_status_id != 22
  AND client_id NOT IN (44725, 51147, 54112, 52552, 52921, 46247)
  AND (cc.offer_date IS NOT NULL OR cd.contract_id IS NOT NULL)
order by number, activation_dt

SQL;

        return $sql;
    }

    private function getUsages()
    {
        $all = \Yii::$app->db->createCommand($this->getPhonesSql())->queryAll();

        $data = [];
        foreach ($all as $v) {
//            if ($v['number'] != 74997060065) {
//                continue;
//            }

            if (!isset($data[$v['number']])) {
                $data[$v['number']] = [];
            }
            $data[$v['number']][] = $v;
        }
        return $data;
    }
}
