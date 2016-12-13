<?php

namespace app\classes\uu\converter;

use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPeriod;
use app\classes\uu\model\TariffResource;

/**
 */
class TariffConverterWelltimeSaas extends TariffConverterA
{
    /**
     * Создать временную таблицу для конвертации тарифа
     */
    protected function createTemporaryTableForTariff()
    {
        $tariffTableName = Tariff::tableName();

        $serviceTypeIdOld = ServiceType::ID_WELLTIME_PRODUCT;
        $serviceTypeId = ServiceType::ID_WELLTIME_SAAS;

        $deltaTariffOld = Tariff::DELTA_WELLTIME_PRODUCT;
        $deltaTariff = Tariff::DELTA_WELLTIME_SAAS;

        // подготовить старые тарифы
        $this->execute("CREATE TEMPORARY TABLE tariff_tmp
            SELECT
                id - {$deltaTariffOld} + {$deltaTariff} as id, {$serviceTypeId} as service_type_id, currency_id, name, tariff_status_id, is_include_vat, tariff_person_id, country_id,
                is_autoprolongation, is_charge_after_period, is_charge_after_blocking, count_of_validity_period,
                insert_user_id, insert_time, update_user_id, update_time
            FROM {$tariffTableName}
            WHERE
                service_type_id = {$serviceTypeIdOld}
        ");
    }

    /**
     * Создать временную таблицу для конвертации периодов тарифа
     */
    protected function createTemporaryTableForTariffPeriod()
    {
        $tariffTableName = Tariff::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();

        $serviceTypeIdOld = ServiceType::ID_WELLTIME_PRODUCT;

        $deltaTariffOld = Tariff::DELTA_WELLTIME_PRODUCT;
        $deltaTariff = Tariff::DELTA_WELLTIME_SAAS;

        $this->execute("CREATE TEMPORARY TABLE tariff_period_tmp
           SELECT
                tariff_period.price_per_period, 
                tariff_period.price_setup, 
                tariff_period.price_min, 
                tariff_period.tariff_id - {$deltaTariffOld} + {$deltaTariff} as tariff_id, 
                tariff_period.period_id, 
                tariff_period.charge_period_id
           FROM {$tariffPeriodTableName} tariff_period,
                {$tariffTableName} tariff
           WHERE
                tariff.id = tariff_period.tariff_id
                AND tariff.service_type_id = {$serviceTypeIdOld}
        ");
    }

    /**
     * Создать временную таблицу для конвертации ресурсов тарифа
     */
    protected function createTemporaryTableForTariffResource()
    {
        $tariffTableName = Tariff::tableName();
        $tariffResourceTableName = TariffResource::tableName();

        $serviceTypeIdOld = ServiceType::ID_WELLTIME_PRODUCT;

        $deltaTariffOld = Tariff::DELTA_WELLTIME_PRODUCT;
        $deltaTariff = Tariff::DELTA_WELLTIME_SAAS;

        $this->execute("CREATE TEMPORARY TABLE tariff_resource_tmp
            (
                `amount` float NOT NULL DEFAULT '0',
                `price_per_unit` float NOT NULL DEFAULT '0',
                `price_min` float NOT NULL DEFAULT '0',
                `resource_id` int(11) NOT NULL,
                `tariff_id` int(11) NOT NULL
            )
        ");

        $this->execute("INSERT INTO tariff_resource_tmp
           SELECT
                tariff_resource.amount, 
                tariff_resource.price_per_unit, 
                tariff_resource.price_min, 
                tariff_resource.resource_id, 
                tariff_resource.tariff_id - {$deltaTariffOld} + {$deltaTariff} as tariff_id
           FROM {$tariffResourceTableName} tariff_resource,
                {$tariffTableName} tariff
           WHERE
                tariff.id = tariff_resource.tariff_id
                AND tariff.service_type_id = {$serviceTypeIdOld}
        ");

        return true;
    }
}
