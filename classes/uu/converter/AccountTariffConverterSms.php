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
class AccountTariffConverterSms extends AccountTariffConverterA
{
    /**
     * Создать временную таблицу для конвертации услуги
     */
    protected function createTemporaryTableForAccountTariff()
    {
        $deltaAccountTariff = AccountTariff::DELTA_SMS;
        $serviceTypeId = ServiceType::ID_SMS;

        // подготовить старые услуги
        $this->execute("CREATE TEMPORARY TABLE account_tariff_tmp
            SELECT 
                usage_sms.id + {$deltaAccountTariff} AS id,
                clients.id AS client_account_id,
                {$serviceTypeId} AS service_type_id,
                NULL AS region_id,
                NULL AS prev_account_tariff_id,
                '' AS comment,
                null AS voip_number
            FROM usage_sms, clients
            WHERE usage_sms.client = clients.client
        ");
    }

    /**
     * Конвертировать лог тарифов
     * @return int
     */
    protected function insertIntoAccountTariffLog()
    {
        $deltaAccountTariff = AccountTariff::DELTA_SMS;
        $deltaTariff = Tariff::DELTA_SMS;
        $accountTariffLogTableName = AccountTariffLog::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();
        $middleDate = UsageInterface::MIDDLE_DATE;

        // лог тарифов 1-в-1 from
        $count1 = $this->execute("INSERT INTO {$accountTariffLogTableName}
          (actual_from_utc, account_tariff_id, tariff_period_id,
          insert_user_id, insert_time)

  SELECT COALESCE(usage_sms.activation_dt, usage_sms.actual_from), usage_sms.id + {$deltaAccountTariff}, {$tariffPeriodTableName}.id,
      null, COALESCE(usage_sms.activation_dt, usage_sms.actual_from)

  FROM usage_sms,
    clients,
    {$tariffPeriodTableName}
  WHERE usage_sms.client = clients.client
    AND usage_sms.tarif_id + {$deltaTariff} = {$tariffPeriodTableName}.tariff_id
    ");

        // лог тарифов 1-в-1 to
        $count2 = $this->execute("INSERT INTO {$accountTariffLogTableName}
          (actual_from_utc, account_tariff_id, tariff_period_id,
          insert_user_id, insert_time)

  SELECT usage_sms.expire_dt, usage_sms.id + {$deltaAccountTariff}, null,
      null, COALESCE(usage_sms.activation_dt, usage_sms.actual_from)

  FROM usage_sms,
    clients,
    {$tariffPeriodTableName}
  WHERE usage_sms.client = clients.client
    AND usage_sms.tarif_id + {$deltaTariff} = {$tariffPeriodTableName}.tariff_id
    AND usage_sms.actual_to IS NOT NULL
    AND usage_sms.actual_to < '{$middleDate}'
    ");

        return $count1 + $count2;
    }
}