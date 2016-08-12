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
class TariffConverterVpbx extends TariffConverterA
{
    /**
     * Создать временную таблицу для конвертации тарифа
     */
    protected function createTemporaryTableForTariff()
    {
        // выбрать уникальные имена тарифов (раньше тарифы дублировались с добавлением услуг в название)
        $statusIdPublic = TariffStatus::ID_PUBLIC;
        $statusIdSpecial = TariffStatus::ID_SPECIAL;
        $statusIdArchive = TariffStatus::ID_ARCHIVE;

        $groupIdAll = TariffPerson::ID_ALL;

        $countryRussia = Country::RUSSIA;
        $countryHungary = Country::HUNGARY;

        $serviceTypeIdVpbx = ServiceType::ID_VPBX;
        $deltaVpbxTariff = Tariff::DELTA_VPBX;

        // подготовить старые тарифы
        $this->execute("CREATE TEMPORARY TABLE tariff_tmp
            SELECT
                id + {$deltaVpbxTariff} AS id,
                {$serviceTypeIdVpbx} AS service_type_id,
                currency AS currency_id,
                description AS name,
                CASE status
                WHEN 'public' THEN {$statusIdPublic}
                WHEN 'special' THEN {$statusIdSpecial}
                WHEN 'archive' THEN {$statusIdArchive}
                END AS tariff_status_id,
                price_include_vat AS is_include_vat,
                {$groupIdAll} AS tariff_person_id,
                IF(currency = 'HUF', {$countryHungary}, {$countryRussia}) AS country_id,
                IF(description LIKE 'Тест%' OR description LIKE 'test%', 0, 1) AS is_autoprolongation,
                1 AS is_charge_after_period,
                1 AS is_charge_after_blocking,
                0 AS count_of_validity_period,
                edit_user AS insert_user_id,
                edit_time AS insert_time,
                edit_user AS update_user_id,
                edit_time AS update_time,
                NULL AS voip_tarificate_id
            FROM
                tarifs_virtpbx
            WHERE
                (description NOT LIKE '%+%' OR description = 'Тариф Лайт+Запись')
                AND description != 'Запись разговоров'
                AND description != 'Дисковое пространство 1 Gb'
                AND description != 'Виртуальный Факс'
                AND description != 'Дополнительный внутренний пользователь'
        ");
    }

    /**
     * Создать временную таблицу для конвертации периодов тарифа
     */
    protected function createTemporaryTableForTariffPeriod()
    {
        $periodIdMonth = Period::ID_MONTH;
        $tariffTableName = Tariff::tableName();
        $serviceTypeId = ServiceType::ID_VPBX;

        $this->execute("CREATE TEMPORARY TABLE tariff_period_tmp
            SELECT
                old.price AS price_per_period,
                0 AS price_setup,
                0 AS price_min,
                new.id AS tariff_id,
                {$periodIdMonth} AS period_id,
                {$periodIdMonth} AS charge_period_id
            FROM
                tarifs_virtpbx old,
                {$tariffTableName} new
            WHERE
                old.description = new.name
                AND new.service_type_id = {$serviceTypeId}
        ");
    }

    /**
     * Создать временную таблицу для конвертации ресурсов тарифа
     */
    protected function createTemporaryTableForTariffResource()
    {
        $serviceTypeIdVpbx = ServiceType::ID_VPBX;
        $tariffTableName = Tariff::tableName();
        $serviceTypeId = ServiceType::ID_VPBX;

        // ВАТС. Абоненты
        $resourceIdAbonent = Resource::ID_VPBX_ABONENT;
        $this->execute("CREATE TEMPORARY TABLE tariff_resource_tmp
            SELECT 
                num_ports AS amount, 
                overrun_per_port AS price_per_unit,
                0 AS price_min, 
                {$resourceIdAbonent} AS resource_id, 
                new.id AS tariff_id
            FROM tarifs_virtpbx old, {$tariffTableName} new
            WHERE old.description = new.name
                AND new.service_type_id = {$serviceTypeId}
        ");

        // ВАТС. Дисковое пространство
        $resourceIdDisk = Resource::ID_VPBX_DISK;
        $this->execute("INSERT INTO tariff_resource_tmp
            SELECT
                space/1024 AS amount,
                overrun_per_gb AS price_per_unit,
                0 AS price_min,
                {$resourceIdDisk} AS resource_id,
                new.id AS tariff_id
            FROM tarifs_virtpbx old, {$tariffTableName} new
            WHERE old.description = new.name
                AND new.service_type_id = {$serviceTypeId}
        ");

        // ВАТС. Подключение номера другого оператора
        // @todo добавляю стоимость 190 у.е. Очевидно, что в разных странах разная, но пока никто не знает, какая именно. Поэтому менеджер потом вручную исправит
        $resourceIdExtDid = Resource::ID_VPBX_EXT_DID;
        $this->execute("INSERT INTO tariff_resource_tmp
            SELECT
                0 AS amount,
                190 AS price_per_unit, 
                0 AS price_min, 
                {$resourceIdExtDid} AS resource_id, 
                {$tariffTableName}.id AS tariff_id
            FROM {$tariffTableName}
            WHERE {$tariffTableName}.service_type_id = {$serviceTypeIdVpbx}
        ");

        // ВАТС. Запись звонков с сайта
        $resourceIdRecord = Resource::ID_VPBX_RECORD;
        $this->execute("INSERT INTO tariff_resource_tmp
            SELECT 
                is_record AS amount, 
                590 AS price_per_unit, 
                0 AS price_min, 
                {$resourceIdRecord} AS resource_id, 
                new.id AS tariff_id
            FROM tarifs_virtpbx old, {$tariffTableName} new
            WHERE old.description = new.name
                AND new.service_type_id = {$serviceTypeId}
        ");

        // ВАТС. Звонки с сайта
        $resourceIdWebCall = Resource::ID_VPBX_WEB_CALL;
        $this->execute("INSERT INTO tariff_resource_tmp
            SELECT 
                is_web_call AS amount, 
                354 AS price_per_unit, 
                0 AS price_min, 
                {$resourceIdWebCall} AS resource_id, 
                new.id AS tariff_id
            FROM tarifs_virtpbx old, {$tariffTableName} new
            WHERE old.description = new.name
                AND new.service_type_id = {$serviceTypeId}
        ");

        // ВАТС. Факс
        $resourceIdFax = Resource::ID_VPBX_FAX;
        $this->execute("INSERT INTO tariff_resource_tmp
            SELECT 
                is_web_call AS amount, 
                118 AS price_per_unit, 
                0 AS price_min, 
                {$resourceIdFax} AS resource_id, 
                new.id AS tariff_id
            FROM tarifs_virtpbx old, {$tariffTableName} new
            WHERE old.description = new.name
                AND new.service_type_id = {$serviceTypeId}
        ");

        return true;
    }

    /**
     * Постобработка
     */
    protected function postProcessing()
    {
        // привести названия в красивый вид
        $tariffTableName = Tariff::tableName();
        $affectedRows = $this->execute("UPDATE {$tariffTableName}
            SET name = 'Тариф Лайт плюс Запись'
            WHERE name = 'Тариф Лайт+Запись'
        ");
        printf('updated = %d, ', $affectedRows);

        $affectedRows = $this->execute("UPDATE {$tariffTableName}
            SET name = REPLACE(name, '  ', ' ')
            WHERE name LIKE '%  %'
        ");
        printf('updated = %d', $affectedRows);
    }
}

