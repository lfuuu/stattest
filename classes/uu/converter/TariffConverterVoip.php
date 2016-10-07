<?php

namespace app\classes\uu\converter;

use app\classes\uu\model\Period;
use app\classes\uu\model\Resource;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPerson;
use app\classes\uu\model\TariffStatus;
use app\classes\uu\model\TariffVoipCity;
use app\classes\uu\model\TariffVoipTarificate;
use Yii;

/**
 */
class TariffConverterVoip extends TariffConverterA
{
    /**
     * Создать временную таблицу для конвертации тарифа
     */
    protected function createTemporaryTableForTariff()
    {
        $statusIdPublic = TariffStatus::ID_PUBLIC;
        $statusIdSpecial = TariffStatus::ID_SPECIAL;
        $statusIdArchive = TariffStatus::ID_ARCHIVE;
        $statusIdTest = TariffStatus::ID_TEST;
        $statusId8800 = TariffStatus::ID_VOIP_8800;
        $statusId8800Test = TariffStatus::ID_VOIP_8800_TEST;
        $statusIdOperator = TariffStatus::ID_VOIP_OPERATOR;
        $statusIdTransit = TariffStatus::ID_VOIP_TRANSIT;

        $personIdAll = TariffPerson::ID_ALL;
        $serviceTypeIdVoip = ServiceType::ID_VOIP;
        $deltaVoipTariff = Tariff::DELTA_VOIP;

        $tarificateBySecond = TariffVoipTarificate::ID_VOIP_BY_SECOND;
        $tarificateBySecondFree = TariffVoipTarificate::ID_VOIP_BY_SECOND_FREE;
        $tarificateByMinute = TariffVoipTarificate::ID_VOIP_BY_MINUTE;
        $tarificateByMinuteFree = TariffVoipTarificate::ID_VOIP_BY_MINUTE_FREE;

        // подготовить старые тарифы
        $this->execute("CREATE TEMPORARY TABLE tariff_tmp
            SELECT
                id + {$deltaVoipTariff} AS id,
                {$serviceTypeIdVoip} AS service_type_id,
                currency_id,
                name,
                CASE status
                    WHEN 'public' THEN {$statusIdPublic}
                    WHEN 'special' THEN {$statusIdSpecial}
                    WHEN 'archive' THEN {$statusIdArchive}
                    WHEN 'test' THEN {$statusIdTest}
                    WHEN '7800' THEN {$statusId8800}
                    WHEN '7800_test' THEN {$statusId8800Test}
                    WHEN 'operator' THEN {$statusIdOperator}
                    WHEN 'transit' THEN {$statusIdTransit}
                END AS tariff_status_id,
                price_include_vat AS is_include_vat,
                {$personIdAll} AS tariff_person_id,
                country_id,
                1 AS is_autoprolongation,
                0 AS is_charge_after_period,
                0 AS is_charge_after_blocking,
                0 AS count_of_validity_period,
                edit_user AS insert_user_id,
                edit_time AS insert_time,
                edit_user AS update_user_id,
                edit_time AS update_time,
                IF(tariffication_by_minutes=0,
                    IF(tariffication_free_first_seconds=0, {$tarificateBySecond}, {$tarificateBySecondFree}),
                    IF(tariffication_free_first_seconds=0, {$tarificateByMinute}, {$tarificateByMinuteFree})
                ) AS voip_tarificate_id
            FROM tarifs_voip
        ");
    }

    /**
     * Создать временную таблицу для конвертации периодов тарифа
     */
    protected function createTemporaryTableForTariffPeriod()
    {
        $periodIdMonth = Period::ID_MONTH;
        $deltaVoipTariff = Tariff::DELTA_VOIP;

        $this->execute("CREATE TEMPORARY TABLE tariff_period_tmp
            SELECT 
                month_number AS price_per_period, 
                once_number AS price_setup, 
                0 AS price_min, 
                id + {$deltaVoipTariff} AS tariff_id, 
                {$periodIdMonth} AS period_id,
                {$periodIdMonth} AS charge_period_id
            FROM tarifs_voip
        ");
    }

    /**
     * Создать временную таблицу для конвертации ресурсов тарифа
     */
    protected function createTemporaryTableForTariffResource()
    {
        $deltaVoipTariff = Tariff::DELTA_VOIP;

        $this->execute("CREATE TEMPORARY TABLE tariff_resource_tmp
            (
                `amount` float NOT NULL DEFAULT '0',
                `price_per_unit` float NOT NULL DEFAULT '0',
                `price_min` float NOT NULL DEFAULT '0',
                `resource_id` int(11) NOT NULL,
                `tariff_id` int(11) NOT NULL
            )
        ");

        // Телефония. Линия
        $resourceIdLine = Resource::ID_VOIP_LINE;
        $this->execute("INSERT INTO tariff_resource_tmp
            SELECT
                1 AS amount,
                month_line AS price_per_unit, 
                0 AS price_min,
                {$resourceIdLine} AS resource_id,
                id + {$deltaVoipTariff} AS tariff_id
            FROM tarifs_voip
        ");

        // Телефония. Звонки
        $resourceIdCalls = Resource::ID_VOIP_CALLS;
        $this->execute("INSERT INTO tariff_resource_tmp
            SELECT
                0 AS amount, 
                1 AS price_per_unit, 
                0 AS price_min,
                {$resourceIdCalls} AS resource_id,
                id + {$deltaVoipTariff} AS tariff_id
            FROM tarifs_voip
        ");

        return true;
    }

    /**
     * Постобработка
     * регионы сконвертировать в города
     */
    protected function postProcessing()
    {
        $deltaTariff = Tariff::DELTA;
        $deltaVoipTariff = Tariff::DELTA_VOIP;
        $tariffTableName = Tariff::tableName();
        $tariffVoipCityTableName = TariffVoipCity::tableName();
        $serviceTypeId = ServiceType::ID_VOIP;

        // Удалить
        $this->execute("DELETE
            tariff_voip_city.*
        FROM
            {$tariffTableName} tariff,
            {$tariffVoipCityTableName} tariff_voip_city
        WHERE
            tariff_voip_city.tariff_id = tariff.id
            AND tariff.service_type_id = {$serviceTypeId}
            AND tariff.id < {$deltaTariff}
        ");

        // добавить заново
        $affectedRows = $this->execute("INSERT INTO {$tariffVoipCityTableName} (tariff_id, city_id)
            SELECT
                tarifs_voip.id + {$deltaVoipTariff} AS tariff_id, 
                city.id AS city_id
            FROM tarifs_voip, city
            WHERE tarifs_voip.connection_point_id = city.connection_point_id
        ");
        printf('updated = %d', $affectedRows);
    }
}
