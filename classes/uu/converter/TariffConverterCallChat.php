<?php

namespace app\classes\uu\converter;

use app\classes\uu\model\Period;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPerson;
use app\classes\uu\model\TariffStatus;
use app\models\Country;

/**
 */
class TariffConverterCallChat extends TariffConverterA
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

        $countryIdRussia = Country::RUSSIA;
        $countryIdHungary = Country::HUNGARY;

        $serviceTypeId = ServiceType::ID_CALL_CHAT;
        $personIdAll = TariffPerson::ID_ALL;
        $deltaTariff = Tariff::DELTA_CALL_CHAT;

        // подготовить старые тарифы
        $this->execute("CREATE TEMPORARY TABLE tariff_tmp
           SELECT
                id + {$deltaTariff} AS id,
                {$serviceTypeId} AS service_type_id,
                currency_id AS currency_id,
                description AS name,
                CASE status
                    WHEN 'public' THEN {$statusIdPublic}
                    WHEN 'special' THEN {$statusIdSpecial}
                    WHEN 'archive' THEN {$statusIdArchive}
                    WHEN 'test' THEN {$statusIdTest}
                END AS tariff_status_id,
                price_include_vat AS is_include_vat,
                {$personIdAll} AS tariff_person_id,
                CASE currency_id
                    WHEN 'RUB' THEN {$countryIdRussia}
                    WHEN 'HUF' THEN {$countryIdHungary}
                END AS country_id,
                1 AS is_autoprolongation,
                0 AS is_charge_after_period,
                0 AS is_charge_after_blocking,
                0 AS count_of_validity_period,
                IF(edit_user>0, edit_user, NULL) AS insert_user_id,
                edit_time AS insert_time,
                IF(edit_user>0, edit_user, NULL) AS update_user_id,
                edit_time AS update_time
            FROM tarifs_call_chat
        ");
    }

    /**
     * Создать временную таблицу для конвертации периодов тарифа
     */
    protected function createTemporaryTableForTariffPeriod()
    {
        $periodIdMonth = Period::ID_MONTH;
        $deltaTariff = Tariff::DELTA_CALL_CHAT;

        $this->execute("CREATE TEMPORARY TABLE tariff_period_tmp
            SELECT
                price AS price_per_period, 
                0 AS price_setup, 
                0 AS price_min, 
                id + {$deltaTariff} AS tariff_id, 
                {$periodIdMonth} AS period_id, 
                {$periodIdMonth} AS charge_period_id
            FROM tarifs_call_chat
        ");
    }
}

