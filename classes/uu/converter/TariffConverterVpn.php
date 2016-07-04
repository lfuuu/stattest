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
class TariffConverterVpn extends TariffConverterA
{
    /**
     * Создать временную таблицу для конвертации тарифа
     */
    protected function createTemporaryTableForTariff()
    {
        $statusIdPublic = TariffStatus::ID_PUBLIC;
        $statusIdSpecial = TariffStatus::ID_SPECIAL;
        $statusIdArchive = TariffStatus::ID_ARCHIVE;

        $serviceTypeIdVpn = ServiceType::ID_VPN;
        $countryIdRussia = Country::RUSSIA;

        $personIdAll = TariffPerson::ID_ALL;
        $deltaVpnTariff = Tariff::DELTA_VPN;

        // подготовить старые тарифы
        $this->execute("CREATE TEMPORARY TABLE tariff_tmp
            SELECT
                id + {$deltaVpnTariff} AS id,
                {$serviceTypeIdVpn} AS service_type_id,
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
            WHERE type = 'V'
        ");
    }

    /**
     * Создать временную таблицу для конвертации периодов тарифа
     */
    protected function createTemporaryTableForTariffPeriod()
    {
        $periodIdMonth = Period::ID_MONTH;
        $deltaVpnTariff = Tariff::DELTA_VPN;

        $this->execute("CREATE TEMPORARY TABLE tariff_period_tmp
            SELECT 
                pay_month AS price_per_period,
                pay_once AS price_setup,
                0 AS price_min,
                id + {$deltaVpnTariff} AS tariff_id,
                {$periodIdMonth} AS period_id,
                {$periodIdMonth} AS charge_period_id
            FROM tarifs_internet
            WHERE type = 'V'
        ");
    }

    /**
     * Создать временную таблицу для конвертации ресурсов тарифа
     */
    protected function createTemporaryTableForTariffResource()
    {
        $deltaVpnTariff = Tariff::DELTA_VPN;

        // VPN. Трафик
        $resourceIdTraffic = Resource::ID_VPN_TRAFFIC;
        $this->execute("CREATE TEMPORARY TABLE tariff_resource_tmp
            SELECT 
                mb_month AS amount, 
                pay_mb AS price_per_unit, 
                0 AS price_min, 
                {$resourceIdTraffic} AS resource_id, 
                id + {$deltaVpnTariff} AS tariff_id
            FROM tarifs_internet
            WHERE type = 'V'
        ");

        return true;
    }
}

