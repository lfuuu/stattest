<?php

namespace app\classes\uu\converter;

use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPeriod;
use app\models\usages\UsageInterface;

/**
 */
class AccountTariffConverterWelltimeSaas extends AccountTariffConverterA
{
    /**
     * Создать временную таблицу для конвертации услуги
     */
    protected function createTemporaryTableForAccountTariff()
    {
        $deltaAccountTariff = AccountTariff::DELTA_WELLTIME_SAAS;
        $serviceTypeId = ServiceType::ID_WELLTIME_SAAS;

        // подготовить старые услуги
        $this->execute("CREATE TEMPORARY TABLE account_tariff_tmp
            SELECT 
                usage_welltime.id + {$deltaAccountTariff} AS id,
                clients.id AS client_account_id,
                {$serviceTypeId} AS service_type_id,
                NULL AS region_id,
                NULL AS prev_account_tariff_id,
                usage_welltime.comment AS comment,
                null AS voip_number
            FROM usage_welltime, clients
            WHERE usage_welltime.client = clients.client
        ");
    }

    /**
     * Конвертировать лог тарифов
     * @return int
     */
    protected function insertIntoAccountTariffLog()
    {
        $deltaAccountTariff = AccountTariff::DELTA_WELLTIME_SAAS;
        $deltaTariff = Tariff::DELTA_WELLTIME_SAAS;
        $accountTariffLogTableName = AccountTariffLog::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();
        $middleDate = UsageInterface::MIDDLE_DATE;

        // лог тарифов 1-в-1 from
        $count1 = $this->execute("INSERT INTO {$accountTariffLogTableName}
          (actual_from_utc, account_tariff_id, tariff_period_id,
          insert_user_id, insert_time)

  SELECT usage_welltime.activation_dt, usage_welltime.id + {$deltaAccountTariff}, {$tariffPeriodTableName}.id,
      null, usage_welltime.activation_dt

  FROM usage_welltime,
    clients,
    {$tariffPeriodTableName}
  WHERE usage_welltime.client = clients.client
    AND usage_welltime.tarif_id + {$deltaTariff} = {$tariffPeriodTableName}.tariff_id
    ");

        // лог тарифов 1-в-1 to
        $count2 = $this->execute("INSERT INTO {$accountTariffLogTableName}
          (actual_from_utc, account_tariff_id, tariff_period_id,
          insert_user_id, insert_time)

  SELECT usage_welltime.expire_dt, usage_welltime.id + {$deltaAccountTariff}, null,
      null, usage_welltime.activation_dt

  FROM usage_welltime,
    clients,
    {$tariffPeriodTableName}
  WHERE usage_welltime.client = clients.client
    AND usage_welltime.tarif_id + {$deltaTariff} = {$tariffPeriodTableName}.tariff_id
    AND usage_welltime.actual_to IS NOT NULL
    AND usage_welltime.actual_to < '{$middleDate}'
    ");

        return $count1 + $count2;
    }
}