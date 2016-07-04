<?php

namespace app\classes\uu\converter;

use app\classes\uu\model\Period;
use app\classes\uu\model\Resource;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPerson;
use app\classes\uu\model\TariffStatus;
use app\models\Country;
use Yii;

/**
 */
class TariffConverterCollocation extends TariffConverterA
{
    /**
     * Создать временную таблицу для конвертации тарифа
     */
    protected function createTemporaryTableForTariff()
    {
        $statusIdPublic = TariffStatus::ID_PUBLIC;
        $statusIdSpecial = TariffStatus::ID_SPECIAL;
        $statusIdArchive = TariffStatus::ID_ARCHIVE;

        $serviceTypeIdCollocation = ServiceType::ID_COLLOCATION;
        $countryIdRussia = Country::RUSSIA;

        $personIdAll = TariffPerson::ID_ALL;
        $deltaCollocationTariff = Tariff::DELTA_COLLOCATION;

        // подготовить старые тарифы
        $this->execute("CREATE TEMPORARY TABLE tariff_tmp
            SELECT
                id + {$deltaCollocationTariff} AS id,
                {$serviceTypeIdCollocation} AS service_type_id,
                currency AS currency_id,
                name,
                CASE status
                    WHEN 'public' THEN {$statusIdPublic}
                    WHEN 'special' THEN {$statusIdSpecial}
                    WHEN 'archive' THEN {$statusIdArchive}
                END AS tariff_status_id,
                price_include_vat AS is_include_vat,
                {$personIdAll} AS tariff_person_id,
                {$countryIdRussia} AS country_id,
                1 AS is_autoprolongation,
                0 AS is_charge_after_period,
                0 AS is_charge_after_blocking,
                0 AS count_of_validity_period,
                edit_user AS insert_user_id,
                edit_time AS insert_time,
                edit_user AS update_user_id,
                edit_time AS update_time,
                null AS voip_tarificate_id
            FROM tarifs_internet
            WHERE type = 'C'
        ");
    }

    /**
     * Создать временную таблицу для конвертации периодов тарифа
     */
    protected function createTemporaryTableForTariffPeriod()
    {
        $periodIdMonth = Period::ID_MONTH;
        $deltaCollocationTariff = Tariff::DELTA_COLLOCATION;

        $this->execute("CREATE TEMPORARY TABLE tariff_period_tmp
             SELECT
                pay_month AS price_per_period, 
                pay_once AS price_setup, 
                0 AS price_min, 
                id + {$deltaCollocationTariff} AS tariff_id, 
                {$periodIdMonth} AS period_id, 
                {$periodIdMonth} AS charge_period_id
            FROM tarifs_internet
            WHERE type = 'C'
        ");
    }

    /**
     * Создать временную таблицу для конвертации ресурсов тарифа
     */
    protected function createTemporaryTableForTariffResource()
    {
        $deltaCollocationTariff = Tariff::DELTA_COLLOCATION;

        // Collocation. Трафик Russia
        $resourceIdTrafficRussia = Resource::ID_COLLOCATION_TRAFFIC_RUSSIA;
        $this->execute("CREATE TEMPORARY TABLE tariff_resource_tmp
            SELECT
                month_r AS amount,
                pay_r AS price_per_unit,
                0 AS price_min,
                {$resourceIdTrafficRussia} AS resource_id,
                id + {$deltaCollocationTariff} AS tariff_id
            FROM tarifs_internet
            WHERE type = 'C'
        ");

        // Collocation. Трафик Russia2
        $resourceIdTrafficRussia2 = Resource::ID_COLLOCATION_TRAFFIC_RUSSIA2;
        $this->execute("INSERT INTO tariff_resource_tmp
           SELECT
                month_r2 AS amount,
                pay_r2 AS price_per_unit,
                0 AS price_min,
                {$resourceIdTrafficRussia2} AS resource_id,
                id + {$deltaCollocationTariff} AS tariff_id
            FROM tarifs_internet
            WHERE type = 'C'
        ");

        // Collocation. Трафик Foreign
        $resourceIdTrafficForeign = Resource::ID_COLLOCATION_TRAFFIC_FOREINGN;
        $this->execute("INSERT INTO tariff_resource_tmp
           SELECT
                month_f AS amount,
                pay_f AS price_per_unit,
                0 AS price_min,
                {$resourceIdTrafficForeign} AS resource_id,
                id + {$deltaCollocationTariff} AS tariff_id
            FROM tarifs_internet
            WHERE type = 'C'
        ");

        return true;
    }
}

