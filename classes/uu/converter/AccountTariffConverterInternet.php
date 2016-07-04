<?php

namespace app\classes\uu\converter;

use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPeriod;
use Yii;

/**
 */
class AccountTariffConverterInternet extends AccountTariffConverterA
{
    /**
     * Создать временную таблицу для конвертации услуги
     */
    protected function createTemporaryTableForAccountTariff()
    {
        $deltaAccountTariff = AccountTariff::DELTA_INTERNET;
        $serviceTypeId = ServiceType::ID_INTERNET;

        // подготовить старые услуги
        $this->execute("CREATE TEMPORARY TABLE account_tariff_tmp
             SELECT 
                usage_ip_ports.id + {$deltaAccountTariff} AS id,
                clients.id AS client_account_id,
                {$serviceTypeId} AS service_type_id,
                NULL AS region_id,
                NULL AS prev_account_tariff_id,
                '' AS comment,
                null AS voip_number
            FROM usage_ip_ports, clients
            WHERE usage_ip_ports.client = clients.client
       ");
    }

    /**
     * Конвертировать лог тарифов
     * @return int
     */
    protected function insertIntoAccountTariffLog()
    {
        $deltaAccountTariff = AccountTariff::DELTA_INTERNET;
        $deltaTariff = Tariff::DELTA_INTERNET;
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
                ServiceType::ID_INTERNET,
                ServiceType::ID_COLLOCATION,
            ]) . ")
        ");
        printf('before2 = %d, ', $affectedRows);

        // лог тарифов 1-в-1
        return $this->execute("INSERT INTO {$accountTariffLogTableName}
          (actual_from, account_tariff_id, tariff_period_id,
          insert_user_id, insert_time)

  SELECT GREATEST(log_tarif.date_activation, usage_ip_ports.actual_from), usage_ip_ports.id + {$deltaAccountTariff}, {$tariffPeriodTableName}.id,
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
    }
}