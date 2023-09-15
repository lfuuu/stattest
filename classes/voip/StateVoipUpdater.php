<?php

namespace app\classes\voip;

use app\classes\Singleton;

class StateVoipUpdater extends Singleton
{
    private array $sql = [];

    public function update()
    {
        echo PHP_EOL . date('r');

        $this->binLogOff();

        $this->createTable();
        $this->makeActual();

        $this->binLogOff();

        $this->addMissing();
        $this->deleteMissing();
        $this->makeChanges();

        try {
            foreach ($this->sql as $sql) {
//                echo PHP_EOL . $sql;
                preg_match('/^\s*(\w+)\b/', $sql, $m);
                echo PHP_EOL . $m[1] . ': ';
                echo \Yii::$app->db->createCommand($sql)->execute();
            }
        } catch (\Exception $e) {
            $this->_dropTable();
            throw $e;
        }
        $this->_dropTable();
    }

    private function createTable()
    {
        $this->_dropTable();
        $this->sql[] = "CREATE TEMPORARY TABLE state_service_voip_tmp LIKE state_service_voip";
    }

    private function _dropTable()
    {
        \Yii::$app->db->createCommand("DROP TEMPORARY TABLE IF EXISTS state_service_voip_tmp");
    }

    private function makeActual()
    {
        $this->sql[] = <<<SQL

INSERT INTO state_service_voip_tmp
SELECT usage_id,
       client_id,
       e164,
       region                                                 as region,
       actual_from,
       actual_to,
       activation_dt,
       IF(expire_dt > '3000-01-01 00:00:00', NULL, expire_dt) AS expire_dt,
       lines_amount,
       trim(device_address)                                   AS device_address
FROM (
         SELECT u.id                                          AS usage_id,
                c.id                                          AS client_id,
                u.e164,
                v.region,
                actual_from                                   AS actual_from,
                IF(actual_to > '3000-01-01', NULL, actual_to) AS actual_to,
                activation_dt,
                expire_dt,
                no_of_lines                                   as lines_amount,
                u.address                                     AS device_address
         FROM usage_voip u,
              voip_numbers v,
              clients c
         WHERE true
           AND c.client = u.client
           AND u.e164 = v.number

         UNION

         SELECT usage_id,
                client_id,
                e164,
                region,
                cast(activation_dt as date) as actual_from,
                cast(expire_dt as date)     as actual_to,
                activation_dt,
                expire_dt,
                lines_amount,
                device_address
         FROM (
                  SELECT u.id                                                     AS usage_id,
                         client_account_id                                        AS client_id,
                         voip_number                                              AS e164,
                         v.region,
                         (SELECT actual_from_utc + INTERVAL 3 HOUR
                          FROM uu_account_tariff_log
                          WHERE account_tariff_id = u.id
                            AND tariff_period_id IS NOT NULL
                          ORDER BY actual_from_utc
                          LIMIT 1)                                                   activation_dt,
                         (SELECT actual_from_utc + INTERVAL 3 HOUR
                          FROM uu_account_tariff_log
                          WHERE account_tariff_id = u.id
                            AND tariff_period_id IS NULL
                          ORDER BY actual_from_utc DESC
                          LIMIT 1)                                                   expire_dt,
                         (SELECT max(amount)
                          FROM uu_account_tariff_resource_log l
                          WHERE l.account_tariff_id = u.id AND l.resource_id = 7) as lines_amount,
                         u.device_address
                  FROM uu_account_tariff u,
                       voip_numbers v,
                       clients c
                  WHERE true
                    AND u.voip_number = v.number
                    AND c.id = u.client_account_id
                    AND service_type_id = 2
              ) a
     ) a;
SQL;
    }

    private function addMissing()
    {
        $this->sql[] = <<<SQL
insert into state_service_voip
select a.*
from state_service_voip_tmp a
         left join state_service_voip b using (usage_id)
where b.usage_id is null;
SQL;
    }

    private function deleteMissing()
    {
        $this->sql[] = <<<SQL
delete z
from state_service_voip z,
     (
    select a.usage_id
    from state_service_voip a
             left join state_service_voip_tmp b using (usage_id)
    where b.usage_id is null
) a where  a.usage_id = z.usage_id

SQL;
    }

    private function makeChanges()
    {
        $this->sql[] = <<<SQL
update
    state_service_voip s,
    (select b.*
     from state_service_voip a
              join state_service_voip_tmp b using (usage_id)
     where true
       and (
                 a.lines_amount != b.lines_amount
             or coalesce(a.actual_from, '') != coalesce(b.actual_from, '')
             or coalesce(a.expire_dt, '') != coalesce(b.expire_dt, '')
             or coalesce(a.activation_dt, '') != coalesce(b.activation_dt, '')
             or coalesce(a.device_address, '') != coalesce(b.device_address, '')
         )
    ) b
set s.lines_amount = b.lines_amount,
    s.actual_from = b.actual_from,
    s.expire_dt = b.expire_dt,
    s.activation_dt = b.activation_dt,
    s.device_address = b.device_address
where s.usage_id = b.usage_id
SQL;
    }

    private function binLogOn()
    {
        $this->sql[] = 'SET SQL_LOG_BIN=1;';
    }

    private function binLogOff()
    {
        $this->sql[] = 'SET SQL_LOG_BIN=0;';
    }


}