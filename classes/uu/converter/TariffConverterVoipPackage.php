<?php

namespace app\classes\uu\converter;

use app\classes\uu\model\Period;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPerson;
use app\classes\uu\model\TariffStatus;
use app\classes\uu\model\TariffVoipCity;
use Yii;

/**
 */
class TariffConverterVoipPackage extends TariffConverterA
{
    /**
     * Создать временную таблицу для конвертации тарифа
     */
    protected function createTemporaryTableForTariff()
    {
        $statusIdPublic = TariffStatus::ID_PUBLIC;
        $personIdAll = TariffPerson::ID_ALL;
        $serviceTypeIdVoipPackage = ServiceType::ID_VOIP_PACKAGE;
        $deltaVoipPackageTariff = Tariff::DELTA_VOIP_PACKAGE;

        // подготовить старые тарифы
        $this->execute("CREATE TEMPORARY TABLE tariff_tmp
            SELECT
                id + {$deltaVoipPackageTariff} AS id,
                {$serviceTypeIdVoipPackage} AS service_type_id,
                currency_id,
                name,
                {$statusIdPublic} AS tariff_status_id,
                price_include_vat AS is_include_vat,
                {$personIdAll} AS tariff_person_id,
                country_id,
                1 AS is_autoprolongation,
                0 AS is_charge_after_period,
                0 AS is_charge_after_blocking,
                0 AS count_of_validity_period,
                null AS insert_user_id,
                null AS insert_time,
                null AS update_user_id,
                null AS update_time,
                null AS voip_tarificate_id
            FROM tarifs_voip_package
        ");
    }

    /**
     * Создать временную таблицу для конвертации периодов тарифа
     */
    protected function createTemporaryTableForTariffPeriod()
    {
        $periodIdMonth = Period::ID_MONTH;
        $deltaVoipPackageTariff = Tariff::DELTA_VOIP_PACKAGE;

        $this->execute("CREATE TEMPORARY TABLE tariff_period_tmp
            SELECT
                periodical_fee AS price_per_period, 
                0 AS price_setup, 
                min_payment AS price_min, 
                id + {$deltaVoipPackageTariff} AS tariff_id, 
                {$periodIdMonth} AS period_id, 
                {$periodIdMonth} AS charge_period_id
            FROM tarifs_voip_package
        ");
    }

    /**
     * Постобработка
     * регионы сконвертировать в города
     */
    protected function postProcessing()
    {
        $deltaVoipPackageTariff = Tariff::DELTA_VOIP_PACKAGE;
        $tariffTableName = Tariff::tableName();
        $tariffVoipCityTableName = TariffVoipCity::tableName();
        $serviceTypeId = ServiceType::ID_VOIP_PACKAGE;

        // Удалить
        $this->execute("DELETE
            tariff_voip_city.*
        FROM
            {$tariffTableName} tariff,
            {$tariffVoipCityTableName} tariff_voip_city
        WHERE
            tariff_voip_city.tariff_id = tariff.id
            AND tariff.service_type_id = {$serviceTypeId}
        ");

        // добавить заново
        $affectedRows = $this->execute("INSERT INTO {$tariffVoipCityTableName} (tariff_id, city_id)
            SELECT  
                tarifs_voip_package.id + {$deltaVoipPackageTariff} AS tariff_id, 
                city.id AS city_id
            FROM tarifs_voip_package, city
            WHERE tarifs_voip_package.connection_point_id = city.connection_point_id
        ");
        printf('updated = %d', $affectedRows);
    }
}
