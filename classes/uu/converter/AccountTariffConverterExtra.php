<?php

namespace app\classes\uu\converter;

use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPeriod;

/**
 */
class AccountTariffConverterExtra extends AccountTariffConverterA
{
    /**
     * Создать временную таблицу для конвертации услуги
     */
    protected function createTemporaryTableForAccountTariff()
    {
        $deltaAccountTariff = AccountTariff::DELTA_EXTRA;
        $serviceTypeId = ServiceType::ID_EXTRA;

        // подготовить старые услуги
        $this->execute("CREATE TEMPORARY TABLE account_tariff_tmp
            SELECT 
                usage_extra.id + {$deltaAccountTariff} AS id,
                clients.id AS client_account_id,
                {$serviceTypeId} AS service_type_id,
                NULL AS region_id,
                NULL AS prev_account_tariff_id,
                '' AS comment,
                null AS voip_number
            FROM usage_extra, clients
            WHERE usage_extra.client = clients.client
        ");
    }

    /**
     * Конвертировать лог тарифов
     * @return int
     */
    protected function insertIntoAccountTariffLog()
    {
        $deltaAccountTariff = AccountTariff::DELTA_EXTRA;
        $deltaTariff = Tariff::DELTA_EXTRA;
        $accountTariffTableName = AccountTariff::tableName();
        $accountTariffLogTableName = AccountTariffLog::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();

        // удалить старый лог тарифов
        $affectedRows = $this->execute("DELETE
            account_tariff_log.*
        FROM
            {$accountTariffTableName} account_tariff,
            {$accountTariffLogTableName} account_tariff_log
        WHERE
            account_tariff.id = account_tariff_log.account_tariff_id
            AND account_tariff.service_type_id IN (" . implode(', ', [
                ServiceType::ID_IT_PARK,
                ServiceType::ID_DOMAIN,
                ServiceType::ID_MAILSERVER,
                ServiceType::ID_ATS,
                ServiceType::ID_SITE,
                ServiceType::ID_USPD,
                ServiceType::ID_WELLSYSTEM,
                ServiceType::ID_WELLTIME_PRODUCT,
                ServiceType::ID_EXTRA,
                ServiceType::ID_SMS_GATE,
            ]) . ")
        ");
        printf('before2 = %d, ', $affectedRows);

        // лог тарифов 1-в-1 from
        $count1 = $this->execute("INSERT INTO {$accountTariffLogTableName}
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

        // лог тарифов 1-в-1 to
        $count2 = $this->execute("INSERT INTO {$accountTariffLogTableName}
          (actual_from, account_tariff_id, tariff_period_id,
          insert_user_id, insert_time)

  SELECT usage_extra.actual_to, usage_extra.id + {$deltaAccountTariff}, null,
      null, usage_extra.activation_dt

  FROM usage_extra,
    clients,
    {$tariffPeriodTableName}
  WHERE usage_extra.client = clients.client
    AND usage_extra.tarif_id + {$deltaTariff} = {$tariffPeriodTableName}.tariff_id
    AND usage_extra.actual_to IS NOT NULL
    AND usage_extra.actual_to < '2020-01-01'
    ");

        return $count1 + $count2;
    }
}