<?php

use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPeriod;

class m160204_180300_convert_voip_account_tariff extends \app\classes\Migration
{
    /**
     * Накатить
     */
    public function safeUp()
    {
        $this->convertAccountTariff();
        $this->convertAccountTariffLog();
    }

    /**
     * Откатить
     */
    public function safeDown()
    {
        $serviceTypeIdVoip = ServiceType::ID_VOIP;
        $accountTariffTableName = AccountTariff::tableName();

        // AccountTariffLog удалится CASCADE
        $this->update($accountTariffTableName, ['prev_account_tariff_id' => null], 'service_type_id = :service_type_id', [':service_type_id' => $serviceTypeIdVoip]);
        $this->delete($accountTariffTableName, 'service_type_id = :service_type_id', [':service_type_id' => $serviceTypeIdVoip]);

    }

    public function convertAccountTariff()
    {
        $deltaVoipAccountTariff = AccountTariff::DELTA_VOIP;
        $serviceTypeIdVoip = ServiceType::ID_VOIP;
        $accountTariffTableName = AccountTariff::tableName();

        $this->execute("INSERT INTO {$accountTariffTableName}
          (id, client_account_id, service_type_id, region_id, prev_account_tariff_id, comment)

  SELECT usage_voip.id + {$deltaVoipAccountTariff},
      clients.id,
      {$serviceTypeIdVoip},
      usage_voip.region,
      IF(usage_voip.prev_usage_id > 0, usage_voip.prev_usage_id + {$deltaVoipAccountTariff}, NULL),
      usage_voip.address
  FROM usage_voip,
    clients
  WHERE usage_voip.client = clients.client
    ");
    }

    public function convertAccountTariffLog()
    {
        $deltaVoipTariff = Tariff::DELTA_VOIP;
        $deltaVoipAccountTariff = AccountTariff::DELTA_VOIP;
        $serviceTypeIdVoip = ServiceType::ID_VPBX;

        $accountTariffTableName = AccountTariff::tableName();
        $accountTariffLogTableName = AccountTariffLog::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();

        // лог тарифов 1-в-1
        $this->execute("INSERT INTO {$accountTariffLogTableName}
          (actual_from, account_tariff_id, tariff_period_id,
          insert_user_id, insert_time)

  SELECT log_tarif.date_activation, usage_voip.id + {$deltaVoipAccountTariff}, {$tariffPeriodTableName}.id,
      user_users.id, log_tarif.ts

  FROM
    (
    log_tarif,
    usage_voip,
    clients,
    {$tariffPeriodTableName}
    )

  LEFT JOIN user_users
  ON log_tarif.id_user = user_users.id

  WHERE log_tarif.service = 'usage_voip'
    AND log_tarif.date_activation > '2000-01-01'
    AND log_tarif.date_activation < '2020-01-01'
    AND log_tarif.id_service = usage_voip.id
    AND usage_voip.client = clients.client
    AND log_tarif.id_tarif + {$deltaVoipTariff} = {$tariffPeriodTableName}.tariff_id
    ");

        $row = $this->db->createCommand("SELECT
    (
        SELECT COUNT(*)
        FROM log_tarif
        WHERE service = 'usage_voip'
    ) AS count_old,

    (
        SELECT COUNT(*)
        FROM {$accountTariffLogTableName}, {$accountTariffTableName}
        WHERE {$accountTariffLogTableName}.account_tariff_id = {$accountTariffTableName}.id
        AND {$accountTariffTableName}.service_type_id = {$serviceTypeIdVoip}
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

  SELECT actual_to, id + {$deltaVoipAccountTariff}, null,
    null, created
  FROM usage_voip
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
        $serviceTypeIdVoip = ServiceType::ID_VOIP;
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
}