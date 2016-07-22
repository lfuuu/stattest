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
class AccountTariffConverterVpbx extends AccountTariffConverterA
{
    /**
     * Создать временную таблицу для конвертации услуги
     */
    protected function createTemporaryTableForAccountTariff()
    {
        $deltaVpbxAccountTariff = AccountTariff::DELTA_VPBX;
        $serviceTypeIdVpbx = ServiceType::ID_VPBX;

        // подготовить старые услуги
        $this->execute("CREATE TEMPORARY TABLE account_tariff_tmp
            SELECT
                usage_virtpbx.id + {$deltaVpbxAccountTariff} AS id,
                clients.id AS client_account_id,
                {$serviceTypeIdVpbx} AS service_type_id,
                usage_virtpbx.region AS region_id,
                NULL AS prev_account_tariff_id,
                usage_virtpbx.comment AS comment,
                null AS voip_number
            FROM usage_virtpbx, clients
            WHERE usage_virtpbx.client = clients.client
        ");
    }

    /**
     * Конвертировать лог тарифов
     * @return int
     */
    protected function insertIntoAccountTariffLog()
    {
        // лог тарифов 1-в-1
        $deltaVpbxAccountTariff = AccountTariff::DELTA_VPBX;
        $accountTariffLogTableName = AccountTariffLog::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();
        $tariffTableName = Tariff::tableName();
        $middleDate = UsageInterface::MIDDLE_DATE;

        return $this->execute("INSERT INTO {$accountTariffLogTableName}
          (actual_from, account_tariff_id, tariff_period_id,
          insert_user_id, insert_time)

  SELECT GREATEST(log_tarif.date_activation, usage_virtpbx.actual_from), log_tarif.id_service + {$deltaVpbxAccountTariff}, {$tariffPeriodTableName}.id,
    user_users.id, log_tarif.ts
  FROM
    (
    log_tarif,
    usage_virtpbx,
    clients,
    tarifs_virtpbx
    )

  LEFT JOIN user_users
  ON log_tarif.id_user = user_users.id

  LEFT JOIN {$tariffTableName} -- если не нашли тариф, то надо сфаталиться (вставить null в not null). Поэтому left join
    ON

    LOWER(
        REPLACE(
            IF(
              LOCATE('+', tarifs_virtpbx.description) > 0 AND tarifs_virtpbx.description != 'Тариф Лайт+Запись',

              RTRIM(
                LEFT(tarifs_virtpbx.description,
                  LOCATE('+', tarifs_virtpbx.description) - 1
                )
              ),

              REPLACE(tarifs_virtpbx.description, 'Тариф Лайт+Запись', 'Тариф Лайт плюс Запись')
            )
        , '  ', ' ')
    )
    = LOWER({$tariffTableName}.name)
  LEFT JOIN {$tariffPeriodTableName}
    ON {$tariffPeriodTableName}.tariff_id = {$tariffTableName}.id

  WHERE log_tarif.service = 'usage_virtpbx'
    AND log_tarif.id_service = usage_virtpbx.id
    AND usage_virtpbx.client = clients.client
    AND log_tarif.id_tarif = tarifs_virtpbx.id
    AND log_tarif.date_activation < '{$middleDate}'
    ");
    }
}

