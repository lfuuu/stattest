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
class AccountTariffConverterCallChat extends AccountTariffConverterA
{
    /**
     * Создать временную таблицу для конвертации услуги
     */
    protected function createTemporaryTableForAccountTariff()
    {
        $serviceTypeId = ServiceType::ID_CALL_CHAT;
        $deltaAccountTariff = AccountTariff::DELTA_CALL_CHAT;

        // подготовить старые услуги
        $this->execute("CREATE TEMPORARY TABLE account_tariff_tmp
             SELECT 
                usage_call_chat.id + {$deltaAccountTariff} AS id,
                clients.id AS client_account_id,
                {$serviceTypeId} AS service_type_id,
                NULL AS region_id,
                NULL AS prev_account_tariff_id,
                '' AS comment,
                null AS voip_number
            FROM usage_call_chat, clients
            WHERE usage_call_chat.client = clients.client
       ");
    }

    /**
     * Конвертировать лог тарифов
     * @return int
     */
    protected function insertIntoAccountTariffLog()
    {
        $deltaAccountTariff = AccountTariff::DELTA_CALL_CHAT;
        $deltaTariff = Tariff::DELTA_CALL_CHAT;

        $accountTariffTableName = AccountTariff::tableName();
        $accountTariffLogTableName = AccountTariffLog::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();
        $middleDate = UsageInterface::MIDDLE_DATE;

        // удалить старый лог тарифов
        $affectedRows = $this->execute("DELETE
            account_tariff_log.*
        FROM
            {$accountTariffTableName} account_tariff,
            {$accountTariffLogTableName} account_tariff_log
        WHERE
            account_tariff.id = account_tariff_log.account_tariff_id
            AND account_tariff.service_type_id = :service_type_id
        ", [':service_type_id' => ServiceType::ID_CALL_CHAT]);
        printf('before2 = %d, ', $affectedRows);

        // лог тарифов 1-в-1 from
        $count1 = $this->execute("INSERT INTO {$accountTariffLogTableName}
          (actual_from, account_tariff_id, tariff_period_id,
          insert_user_id, insert_time)

  SELECT usage_call_chat.actual_from, usage_call_chat.id + {$deltaAccountTariff}, {$tariffPeriodTableName}.id,
      null, usage_call_chat.actual_from

  FROM
    (
    usage_call_chat,
    clients,
    {$tariffPeriodTableName}
    )

  WHERE usage_call_chat.actual_from BETWEEN '2000-01-01' AND '{$middleDate}'
    AND usage_call_chat.client = clients.client
    AND usage_call_chat.tarif_id + {$deltaTariff} = {$tariffPeriodTableName}.tariff_id
    ");

        // лог тарифов 1-в-1 to
        $count2 = $this->execute("INSERT INTO {$accountTariffLogTableName}
          (actual_from, account_tariff_id, tariff_period_id,
          insert_user_id, insert_time)

  SELECT usage_call_chat.actual_to, usage_call_chat.id + {$deltaAccountTariff}, null,
      null, usage_call_chat.expire_dt

  FROM usage_call_chat, clients
  WHERE usage_call_chat.actual_to < '{$middleDate}'
    AND usage_call_chat.client = clients.client
    ");

        return $count1 + $count2;
    }
}