<?php

use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPeriod;

class m160119_154547_convert_vbpx_account_tariff extends \app\classes\Migration
{
    public function safeUp()
    {
        $this->convertAccountTariff();
        $this->convertAccountTariffLog();
    }

    public function convertAccountTariff()
    {
        $deltaVpbxAccountTariff = AccountTariff::DELTA_VPBX;
        $serviceTypeIdVpbx = ServiceType::ID_VPBX;

        $accountTariffTableName = AccountTariff::tableName();

        $this->execute("INSERT INTO {$accountTariffTableName}
          (id, client_account_id, service_type_id, region_id, prev_account_tariff_id, comment)

  SELECT usage_virtpbx.id + {$deltaVpbxAccountTariff},
      clients.id,
      {$serviceTypeIdVpbx},
      usage_virtpbx.region,
      IF(usage_virtpbx.prev_usage_id > 0, usage_virtpbx.prev_usage_id, NULL),
      usage_virtpbx.comment
  FROM usage_virtpbx,
    clients
  WHERE usage_virtpbx.client = clients.client
    ");
    }

    public function convertAccountTariffLog()
    {
        $deltaVpbxAccountTariff = AccountTariff::DELTA_VPBX;
        $serviceTypeIdVpbx = ServiceType::ID_VPBX;

        $accountTariffTableName = AccountTariff::tableName();
        $accountTariffLogTableName = AccountTariffLog::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();
        $tariffTableName = Tariff::tableName();

        // лог тарифов 1-в-1
        $this->execute("INSERT INTO {$accountTariffLogTableName}
          (actual_from, account_tariff_id, tariff_period_id,
          insert_user_id, insert_time)

  SELECT log_tarif.date_activation, log_tarif.id_service + {$deltaVpbxAccountTariff}, {$tariffPeriodTableName}.id,
    user_users.id, log_tarif.ts
  FROM
    (
    log_tarif,
    usage_virtpbx,
    clients,
    tarifs_virtpbx
    )

  LEFT JOIN user_users
  ON log_tarif.id_user = user_users.id

  LEFT JOIN {$tariffTableName} -- если не нашли тариф, то надо сфаталиться (вставить null в not null). Поэтому left join
    ON

    LOWER(
        REPLACE(
            IF(
              LOCATE('+', tarifs_virtpbx.description) > 0 AND tarifs_virtpbx.description != 'Тариф Лайт+Запись',

              RTRIM(
                LEFT(tarifs_virtpbx.description,
                  LOCATE('+', tarifs_virtpbx.description) - 1
                )
              ),

              REPLACE(tarifs_virtpbx.description, 'Тариф Лайт+Запись', 'Тариф Лайт плюс Запись')
            )
        , '  ', ' ')
    )
    = LOWER({$tariffTableName}.name)
  LEFT JOIN {$tariffPeriodTableName}
    ON {$tariffPeriodTableName}.tariff_id = {$tariffTableName}.id

  WHERE log_tarif.service = 'usage_virtpbx'
    AND log_tarif.id_service = usage_virtpbx.id
    AND usage_virtpbx.client = clients.client
    AND log_tarif.id_tarif = tarifs_virtpbx.id
    AND log_tarif.date_activation < '2020-01-01'
    ");

        $row = $this->db->createCommand("SELECT
    (
        SELECT COUNT(*)
        FROM log_tarif
        WHERE service = 'usage_virtpbx'
    ) AS count_old,

    (
        SELECT COUNT(*)
        FROM {$accountTariffLogTableName}, {$accountTariffTableName}
        WHERE {$accountTariffLogTableName}.account_tariff_id = {$accountTariffTableName}.id
        AND {$accountTariffTableName}.service_type_id = {$serviceTypeIdVpbx}
    ) AS count_new
    ")->queryOne();
        printf('Count before: %d. Count after: %d' . PHP_EOL, $row['count_old'], $row['count_new']);
        // В старом логе тарифов есть несколько записей, ссылающихся на несуществующую услугу. Поэтому они не смогут сконвертироваться
//        if ($row['count_old'] != $row['count_new']) {
//            throw new Exception('Not everything was converted.');
//        }

        // теперь в тарифе нет окончания, это надо брать из лога
        $this->execute("INSERT INTO {$accountTariffLogTableName}
          (actual_from, account_tariff_id, tariff_period_id,
          insert_user_id, insert_time)

  SELECT actual_to, id, null,
    null, NOW()
  FROM usage_virtpbx
  WHERE actual_to BETWEEN '2000-01-01' AND '2020-01-01'");

        // после окончания услуги не должно быть никаких новых логов тарифа
        $this->execute("DELETE active.*
          FROM {$accountTariffLogTableName} active,
              {$accountTariffLogTableName} closed
          WHERE
            closed.tariff_period_id IS NULL
            AND closed.account_tariff_id = active.account_tariff_id
            AND active.actual_from > closed.actual_from
          ");

        // кэш последнего тарифа
        $serviceTypeIdVoip = ServiceType::ID_VPBX;
        $this->execute("UPDATE {$accountTariffTableName}
        SET
          tariff_period_id = (
            SELECT {$accountTariffLogTableName}.tariff_period_id
            FROM {$accountTariffLogTableName}
            WHERE
              {$accountTariffTableName}.id = {$accountTariffLogTableName}.account_tariff_id
            ORDER BY
              {$accountTariffLogTableName}.actual_from DESC,
              {$accountTariffLogTableName}.id DESC
            LIMIT 1
          )
          WHERE
              service_type_id = {$serviceTypeIdVoip}
        ");
    }

    public function safeDown()
    {
        $this->truncateTable(AccountTariffLog::tableName());

        // truncate не получается из-за fk
        $accountTariffTableName = AccountTariff::tableName();
        $this->update($accountTariffTableName, ['prev_account_tariff_id' => null]);
        $this->delete($accountTariffTableName);
    }
}
