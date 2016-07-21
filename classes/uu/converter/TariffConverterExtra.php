<?php

namespace app\classes\uu\converter;

use app\classes\uu\model\Period;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPerson;
use app\classes\uu\model\TariffStatus;
use app\models\Country;
use Yii;

/**
 */
class TariffConverterExtra extends TariffConverterA
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

        $serviceTypeIdItpark = ServiceType::ID_IT_PARK;
        $serviceTypeIdDomain = ServiceType::ID_DOMAIN;
        $serviceTypeIdMailserver = ServiceType::ID_MAILSERVER;
        $serviceTypeIdAts = ServiceType::ID_ATS;
        $serviceTypeIdSite = ServiceType::ID_SITE;
        $serviceTypeIdSms = ServiceType::ID_SMS_GATE;
        $serviceTypeIdUspd = ServiceType::ID_USPD;
        $serviceTypeIdWellsystem = ServiceType::ID_WELLSYSTEM;
        $serviceTypeIdWelltime = ServiceType::ID_WELLTIME_PRODUCT;
        $serviceTypeIdExtra = ServiceType::ID_EXTRA;

        $countryIdRussia = Country::RUSSIA;

        $personIdAll = TariffPerson::ID_ALL;
        $deltaExtraTariff = Tariff::DELTA_EXTRA;

        // подготовить старые тарифы
        $this->execute("CREATE TEMPORARY TABLE tariff_tmp
            SELECT
                tarifs_extra.id + {$deltaExtraTariff} AS id,
                IF(tarifs_extra.status = 'itpark', {$serviceTypeIdItpark},
                    CASE tarifs_extra.code
                        WHEN 'domain' THEN {$serviceTypeIdDomain}
                        WHEN 'ip' THEN {$serviceTypeIdWelltime}
                        WHEN 'mailserver' THEN {$serviceTypeIdMailserver}
                        WHEN 'phone_ats' THEN {$serviceTypeIdAts}
                        WHEN 'site' THEN {$serviceTypeIdSite}
                        WHEN 'sms_gate' THEN {$serviceTypeIdSms}
                        WHEN 'uspd' THEN {$serviceTypeIdUspd}
                        WHEN 'wellsystem' THEN {$serviceTypeIdWellsystem}
                        WHEN 'welltime' THEN {$serviceTypeIdWelltime}
                        WHEN 'workingtable' THEN {$serviceTypeIdItpark}
                        ELSE {$serviceTypeIdExtra}
                    END
                ) AS service_type_id,
                tarifs_extra.currency AS currency_id,
                tarifs_extra.description AS name,
                CASE tarifs_extra.status
                    WHEN 'public' THEN {$statusIdPublic}
                    WHEN 'special' THEN {$statusIdSpecial}
                    WHEN 'archive' THEN {$statusIdArchive}
                    ELSE {$statusIdTest}
                END AS tariff_status_id,
                tarifs_extra.price_include_vat AS is_include_vat,
                {$personIdAll} AS tariff_person_id,
                {$countryIdRussia} AS country_id,
                1 AS is_autoprolongation,
                0 AS is_charge_after_period,
                0 AS is_charge_after_blocking,
                0 AS count_of_validity_period,
                user_users.id AS insert_user_id,
                tarifs_extra.edit_time AS insert_time,
                user_users.id AS update_user_id,
                tarifs_extra.edit_time AS update_time,
                null AS voip_tarificate_id
            FROM tarifs_extra
            LEFT JOIN user_users
                ON tarifs_extra.edit_user = user_users.id
        ");
    }

    /**
     * Создать временную таблицу для конвертации периодов тарифа
     */
    protected function createTemporaryTableForTariffPeriod()
    {
        $periodIdMonth = Period::ID_MONTH;
        $periodIdQuarter = Period::ID_QUARTER;
        $periodIdHalfYear = Period::ID_HALFYEAR;
        $periodIdYear = Period::ID_YEAR;

        $deltaExtraTariff = Tariff::DELTA_EXTRA; // без разницы, какой именно дельта, у всех одинаковый

        $this->execute("CREATE TEMPORARY TABLE tariff_period_tmp
             SELECT
                0 AS price_per_period, 
                price AS price_setup, 
                0 AS price_min, 
                id + {$deltaExtraTariff} AS tariff_id, 
                {$periodIdMonth} AS period_id, 
                {$periodIdYear} AS charge_period_id
            FROM tarifs_extra
            WHERE period = 'once'
        ");

        $this->execute("INSERT INTO tariff_period_tmp
            SELECT
                price AS price_per_period,
                0 AS price_setup,
                0 AS price_min, 
                id + {$deltaExtraTariff} AS tariff_id,
                CASE period
                    WHEN 'month' THEN {$periodIdMonth}
                    WHEN '3mon' THEN {$periodIdQuarter}
                    WHEN '6mon' THEN {$periodIdHalfYear}
                    WHEN 'year' THEN {$periodIdYear}
                END AS period_id,
                CASE period
                    WHEN 'month' THEN {$periodIdMonth}
                    WHEN '3mon' THEN {$periodIdQuarter}
                    WHEN '6mon' THEN {$periodIdHalfYear}
                    WHEN 'year' THEN {$periodIdYear}
                END AS charge_period_id
            FROM tarifs_extra
            WHERE period != 'once'
        ");
    }
}

