<?php
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPeriod;

class m160513_171500_convert_internet_account_tariff extends \app\classes\Migration
{
    /**
     * Накатить
     */
    public function safeUp()
    {
        $deltaAccountTariff = AccountTariff::DELTA_INTERNET;
        $deltaTariff = Tariff::DELTA_INTERNET;
        $serviceTypeId = ServiceType::ID_INTERNET;

        $this->convertAccountTariff($serviceTypeId, $deltaAccountTariff);
        $this->convertAccountTariffLog($serviceTypeId, $deltaAccountTariff, $deltaTariff);
        $this->fixAccountTariffServiceTypeId();
    }

    /**
     * Откатить
     */
    public function safeDown()
    {
        $serviceTypeId = ServiceType::ID_INTERNET;

        $this->deleteFromAccountTariff($serviceTypeId);
    }

    /**
     * Откатить
     */
    protected function deleteFromAccountTariff($serviceTypeId)
    {
        $accountTariffTableName = AccountTariff::tableName();

        // AccountTariffLog удалится CASCADE
        $this->update($accountTariffTableName, ['prev_account_tariff_id' => null]);
        $this->delete($accountTariffTableName, 'service_type_id = :service_type_id',
            [':service_type_id' => $serviceTypeId]);

    }

    /**
     * Конвертировать услуги
     * @param int $serviceTypeId
     * @param int $deltaAccountTariff
     */
    protected function convertAccountTariff($serviceTypeId, $deltaAccountTariff)
    {
        $accountTariffTableName = AccountTariff::tableName();

        $this->execute("INSERT INTO {$accountTariffTableName}
          (id, client_account_id, service_type_id, region_id, prev_account_tariff_id, comment)

  SELECT usage_ip_ports.id + {$deltaAccountTariff},
      clients.id,
      {$serviceTypeId},
      NULL,
      NULL,
      ''
  FROM usage_ip_ports,
    clients
  WHERE usage_ip_ports.client = clients.client
    ");
    }

    /**
     * Конвертировать лог услуг
     * @param int $serviceTypeId
     * @param int $deltaAccountTariff
     * @param int $deltaTariff
     */
    protected function convertAccountTariffLog($serviceTypeId, $deltaAccountTariff, $deltaTariff)
    {
        $accountTariffTableName = AccountTariff::tableName();
        $accountTariffLogTableName = AccountTariffLog::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();

        // лог тарифов 1-в-1
        $this->execute("INSERT INTO {$accountTariffLogTableName}
          (actual_from, account_tariff_id, tariff_period_id,
          insert_user_id, insert_time)

  SELECT log_tarif.date_activation, usage_ip_ports.id + {$deltaAccountTariff}, {$tariffPeriodTableName}.id,
      user_users.id, log_tarif.ts

  FROM
    (
    log_tarif,
    usage_ip_ports,
    clients,
    {$tariffPeriodTableName}
    )

  LEFT JOIN user_users
    ON log_tarif.id_user = user_users.id

  WHERE log_tarif.service = 'usage_ip_ports'
    AND log_tarif.date_activation > '2000-01-01'
    AND log_tarif.date_activation < '2020-01-01'
    AND log_tarif.id_service = usage_ip_ports.id
    AND usage_ip_ports.client = clients.client
    AND log_tarif.id_tarif + {$deltaTariff} = {$tariffPeriodTableName}.tariff_id
    ");

        $row = $this->db->createCommand("SELECT
    (
        SELECT COUNT(*)
        FROM log_tarif
        WHERE service = 'usage_ip_ports'
    ) AS count_old,

    (
        SELECT COUNT(*)
        FROM {$accountTariffLogTableName}, {$accountTariffTableName}
        WHERE {$accountTariffLogTableName}.account_tariff_id = {$accountTariffTableName}.id
        AND {$accountTariffTableName}.service_type_id = {$serviceTypeId}
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

  SELECT usage_ip_ports.actual_to, usage_ip_ports.id + {$deltaAccountTariff}, null,
    null, null
  FROM usage_ip_ports,
    clients
  WHERE usage_ip_ports.client = clients.client
    AND usage_ip_ports.actual_to BETWEEN '2000-01-01' AND '2020-01-01'");


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
              service_type_id = {$serviceTypeId}
        ");
    }

    /**
     * Установить AccountTariff.ServiceTypeId на основе Tariff.ServiceTypeId
     */
    protected function fixAccountTariffServiceTypeId()
    {
        $accountTariffTableName = AccountTariff::tableName();
        $accountTariffLogTableName = AccountTariffLog::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();
        $tariffTableName = Tariff::tableName();

        // для текущего тарифа
        $this->execute("UPDATE 
            {$accountTariffTableName},
            {$tariffPeriodTableName},
            {$tariffTableName}
        SET
          {$accountTariffTableName}.service_type_id = {$tariffTableName}.service_type_id
        WHERE
          {$accountTariffTableName}.tariff_period_id = {$tariffPeriodTableName}.id
          AND {$tariffPeriodTableName}.tariff_id = {$tariffTableName}.id
        ");

        // для закрытого тарифа - по логу
        $this->execute("UPDATE 
            {$accountTariffTableName},
            {$accountTariffLogTableName},
            {$tariffPeriodTableName},
            {$tariffTableName}
        SET
          {$accountTariffTableName}.service_type_id = {$tariffTableName}.service_type_id
        WHERE
          {$accountTariffTableName}.tariff_period_id IS NULL
          AND {$accountTariffTableName}.id = {$accountTariffLogTableName}.account_tariff_id
          AND {$accountTariffLogTableName}.tariff_period_id = {$tariffPeriodTableName}.id
          AND {$tariffPeriodTableName}.tariff_id = {$tariffTableName}.id
        ");
    }
}