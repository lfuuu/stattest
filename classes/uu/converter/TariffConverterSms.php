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
class TariffConverterSms extends TariffConverterA
{
    /**
     * Создать временную таблицу для конвертации тарифа
     */
    protected function createTemporaryTableForTariff()
    {
        $statusIdPublic = TariffStatus::ID_PUBLIC;

        $serviceTypeId = ServiceType::ID_SMS;
        $countryIdRussia = Country::RUSSIA;

        $personIdAll = TariffPerson::ID_ALL;
        $deltaTariff = Tariff::DELTA_SMS;

        // подготовить старые тарифы
        $this->execute("CREATE TEMPORARY TABLE tariff_tmp
            SELECT
                tarifs_sms.id + {$deltaTariff} AS id,
                {$serviceTypeId} AS service_type_id,
                tarifs_sms.currency AS currency_id,
                tarifs_sms.description AS name,
                {$statusIdPublic} AS tariff_status_id,
                tarifs_sms.price_include_vat AS is_include_vat,
                {$personIdAll} AS tariff_person_id,
                {$countryIdRussia} AS country_id,
                1 AS is_autoprolongation,
                0 AS is_charge_after_period,
                0 AS is_charge_after_blocking,
                0 AS count_of_validity_period,
                user_users.id AS insert_user_id,
                tarifs_sms.edit_time AS insert_time,
                user_users.id AS update_user_id,
                tarifs_sms.edit_time AS update_time,
                null AS voip_tarificate_id
            FROM tarifs_sms
            LEFT JOIN user_users
                ON tarifs_sms.edit_user = user_users.id
        ");
    }

    /**
     * Создать временную таблицу для конвертации периодов тарифа
     */
    protected function createTemporaryTableForTariffPeriod()
    {
        $periodIdMonth = Period::ID_MONTH;
        $deltaTariff = Tariff::DELTA_SMS;

        $this->execute("CREATE TEMPORARY TABLE tariff_period_tmp
           SELECT
                per_month_price AS price_per_period,
                0 AS price_setup, 
                0 AS price_min, 
                id + {$deltaTariff} AS tariff_id,
                {$periodIdMonth} AS period_id,
                {$periodIdMonth} AS charge_period_id
            FROM tarifs_sms
        ");
    }

    /**
     * Создать временную таблицу для конвертации ресурсов тарифа
     */
    protected function createTemporaryTableForTariffResource()
    {
        $deltaTariff = Tariff::DELTA_SMS;

        $this->execute("CREATE TEMPORARY TABLE tariff_resource_tmp
            (
                `amount` float NOT NULL DEFAULT '0',
                `price_per_unit` float NOT NULL DEFAULT '0',
                `price_min` float NOT NULL DEFAULT '0',
                `resource_id` int(11) NOT NULL,
                `tariff_id` int(11) NOT NULL
            )
        ");

        // SMS
        $resourceId = Resource::ID_SMS;
        $this->execute("INSERT INTO tariff_resource_tmp
           SELECT
                0 AS amount, 
                per_sms_price AS price_per_unit, 
                0 AS price_min, 
                {$resourceId} AS resource_id, 
                id + {$deltaTariff} AS tariff_id
            FROM tarifs_sms
        ");

        return true;
    }
}
