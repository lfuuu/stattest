<?php

namespace app\classes\uu\converter;

use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPeriod;
use app\models\usages\UsageInterface;
use Yii;

/**
 */
class AccountTariffConverterVoipPackage extends AccountTariffConverterA
{
    /**
     * Создать временную таблицу для конвертации услуги
     */
    protected function createTemporaryTableForAccountTariff()
    {
        $deltaVoipPackageAccountTariff = AccountTariff::DELTA_VOIP_PACKAGE;
        $serviceTypeIdVoipPackage = ServiceType::ID_VOIP_PACKAGE;

        // подготовить старые услуги
        $this->execute("CREATE TEMPORARY TABLE account_tariff_tmp
            SELECT 
                usage_voip_package.id + {$deltaVoipPackageAccountTariff} AS id,
                clients.id AS client_account_id,
                {$serviceTypeIdVoipPackage} AS service_type_id,
                NULL AS region_id,
                NULL prev_account_tariff_id,
                '' AS comment,
                null AS voip_number
            FROM usage_voip_package, clients
            WHERE usage_voip_package.client = clients.client
        ");
    }

    /**
     * Конвертировать лог тарифов
     * @return int
     */
    protected function insertIntoAccountTariffLog()
    {
        $deltaVoipTariffPackage = Tariff::DELTA_VOIP_PACKAGE;
        $deltaVoipAccountTariffPackage = AccountTariff::DELTA_VOIP_PACKAGE;
        $accountTariffLogTableName = AccountTariffLog::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();
        $middleDate = UsageInterface::MIDDLE_DATE;

        // лог тарифов 1-в-1
        return $this->execute("INSERT INTO {$accountTariffLogTableName}
          (actual_from, account_tariff_id, tariff_period_id,
          insert_user_id, insert_time)

  SELECT GREATEST(log_tarif.date_activation, usage_voip_package.actual_from), usage_voip_package.id + {$deltaVoipAccountTariffPackage}, {$tariffPeriodTableName}.id,
      user_users.id, log_tarif.ts

  FROM
    (
    log_tarif,
    usage_voip_package,
    clients,
    {$tariffPeriodTableName}
    )

  LEFT JOIN user_users
  ON log_tarif.id_user = user_users.id

  WHERE log_tarif.service = 'usage_voip_package'
    AND log_tarif.date_activation > '2000-01-01'
    AND log_tarif.date_activation < '{$middleDate}'
    AND log_tarif.id_service = usage_voip_package.id
    AND usage_voip_package.client = clients.client
    AND log_tarif.id_tarif + {$deltaVoipTariffPackage} = {$tariffPeriodTableName}.tariff_id
    ");
    }
}