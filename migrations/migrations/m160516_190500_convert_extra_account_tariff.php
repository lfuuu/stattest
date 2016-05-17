<?php
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPeriod;

class m160516_190500_convert_extra_account_tariff extends \app\classes\Migration
{
    /**
     * Накатить
     */
    public function safeUp()
    {
        $deltaAccountTariff = AccountTariff::DELTA_EXTRA;
        $deltaTariff = Tariff::DELTA_EXTRA;
        $serviceTypeId = ServiceType::ID_EXTRA;

        $this->convertAccountTariff($serviceTypeId, $deltaAccountTariff);
        $this->convertAccountTariffLog($serviceTypeId, $deltaAccountTariff, $deltaTariff);
        $this->fixAccountTariffServiceTypeId();
    }

    /**
     * Откатить
     */
    public function safeDown()
    {
        $serviceTypeId = ServiceType::ID_EXTRA;

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

  SELECT usage_extra.id + {$deltaAccountTariff},
      clients.id,
      {$serviceTypeId},
      NULL,
      NULL,
      ''
  FROM usage_extra,
    clients
  WHERE usage_extra.client = clients.client
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

  SELECT usage_extra.actual_from, usage_extra.id + {$deltaAccountTariff}, {$tariffPeriodTableName}.id,
      null, usage_extra.activation_dt

  FROM usage_extra,
    clients,
    {$tariffPeriodTableName}
  WHERE usage_extra.client = clients.client
    AND usage_extra.tarif_id + {$deltaTariff} = {$tariffPeriodTableName}.tariff_id
    ");

        // теперь в тарифе нет окончания, это надо брать из лога
        $this->execute("INSERT INTO {$accountTariffLogTableName}
          (actual_from, account_tariff_id, tariff_period_id,
          insert_user_id, insert_time)

  SELECT usage_extra.actual_to, usage_extra.id + {$deltaAccountTariff}, null,
    null, null
  FROM usage_extra,
    clients
  WHERE usage_extra.client = clients.client
    AND usage_extra.actual_to BETWEEN '2000-01-01' AND '2020-01-01'");


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