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

        // лог тарифов 1-в-1
        return $this->execute("INSERT INTO {$accountTariffLogTableName}
          (actual_from, account_tariff_id, tariff_period_id,
          insert_user_id, insert_time)

  SELECT usage_sms.actual_from, usage_sms.id + {$deltaAccountTariff}, {$tariffPeriodTableName}.id,
      null, usage_sms.activation_dt

  FROM usage_sms,
    clients,
    {$tariffPeriodTableName}
  WHERE usage_sms.client = clients.client
    AND usage_sms.tarif_id + {$deltaTariff} = {$tariffPeriodTableName}.tariff_id
    ");
    }
}