<?php

namespace app\classes\uu\converter;

use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPeriod;
use app\models\City;
use Yii;

/**
 */
class AccountTariffConverterVoip extends AccountTariffConverterA
{
    /**
     * Создать временную таблицу для конвертации услуги
     */
    protected function createTemporaryTableForAccountTariff()
    {
        $deltaVoipAccountTariff = AccountTariff::DELTA_VOIP;
        $serviceTypeIdVoip = ServiceType::ID_VOIP;

        // подготовить старые услуги
        $this->execute("CREATE TEMPORARY TABLE account_tariff_tmp
            SELECT
                usage_voip.id + {$deltaVoipAccountTariff} AS id,
                clients.id AS client_account_id,
                {$serviceTypeIdVoip} AS service_type_id,
                usage_voip.region AS region_id,
                null AS prev_account_tariff_id,
                usage_voip.address AS comment,
                E164 AS voip_number
            FROM usage_voip, clients
            WHERE usage_voip.client = clients.client
        ");
    }

    /**
     * Конвертировать лог тарифов
     * @return int
     */
    protected function insertIntoAccountTariffLog()
    {
        $deltaVoipTariff = Tariff::DELTA_VOIP;
        $deltaVoipAccountTariff = AccountTariff::DELTA_VOIP;
        $accountTariffLogTableName = AccountTariffLog::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();

        // лог тарифов 1-в-1
        return $this->execute("INSERT INTO {$accountTariffLogTableName}
          (actual_from, account_tariff_id, tariff_period_id,
          insert_user_id, insert_time)

  SELECT GREATEST(log_tarif.date_activation, usage_voip.actual_from), usage_voip.id + {$deltaVoipAccountTariff}, {$tariffPeriodTableName}.id,
      user_users.id, log_tarif.ts

  FROM
    (
    log_tarif,
    usage_voip,
    clients,
    {$tariffPeriodTableName}
    )

  LEFT JOIN user_users
  ON log_tarif.id_user = user_users.id

  WHERE log_tarif.service = 'usage_voip'
    AND log_tarif.date_activation > '2000-01-01'
    AND log_tarif.date_activation < '2020-01-01'
    AND log_tarif.id_service = usage_voip.id
    AND usage_voip.client = clients.client
    AND log_tarif.id_tarif + {$deltaVoipTariff} = {$tariffPeriodTableName}.tariff_id
    ");
    }

    /**
     * Постобработка
     */
    protected function postProcessing()
    {
        parent::postProcessing();

        $cityTableName = City::tableName();
        $accountTableName = AccountTariff::tableName();
        $sql = <<<SQL
UPDATE
    {$accountTableName} account_tariff,
    {$cityTableName} city
SET
    account_tariff.city_id = city.id
WHERE
    account_tariff.city_id IS NULL
    AND account_tariff.region_id = city.connection_point_id
SQL;
        $this->execute($sql);

    }
}