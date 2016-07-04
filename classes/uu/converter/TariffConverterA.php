<?php

namespace app\classes\uu\converter;

use app\classes\Connection;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPeriod;
use app\classes\uu\model\TariffResource;
use Yii;

/**
 */
abstract class TariffConverterA
{
    /**
     * Создать временную таблицу для конвертации тарифа
     */
    protected abstract function createTemporaryTableForTariff();

    /**
     * Создать временную таблицу для конвертации периодов тарифа
     */
    protected abstract function createTemporaryTableForTariffPeriod();

    /**
     * Executes a SQL statement.
     * This method executes the specified SQL statement using [[db]].
     * @param string $sql the SQL statement to be executed
     * @param array $params input parameters (name => value) for the SQL execution.
     * See [[Command::execute()]] for more details.
     */
    protected function execute($sql, $params = [])
    {
        /** @var Connection $db */
        $db = Yii::$app->db;
        return $db->createCommand($sql)->bindValues($params)->execute();
    }

    /**
     * Доконвертировать тариф
     * @param int $serviceTypeId
     */
    public function convert($serviceTypeId)
    {
        echo PHP_EOL . 'ServiceTypeId = ' . $serviceTypeId;

        echo PHP_EOL . 'convertTariff: ';
        $this->convertTariff();

        echo PHP_EOL . 'convertTariffPeriod: ';
        $this->convertTariffPeriod();

        echo PHP_EOL . 'convertTariffResource: ';
        $this->convertTariffResource();

        echo PHP_EOL . 'postProcessing: ';
        $this->postProcessing();

        echo PHP_EOL;
    }

    /**
     * Доконвертировать Tariff
     */
    protected function convertTariff()
    {
        // Создать временную таблицу для конвертации тарифа
        $this->createTemporaryTableForTariff();

        $tariffTableName = Tariff::tableName();

        // обновить ранее конвертированные универсальные тарифы
        $affectedRows = $this->execute("UPDATE
            {$tariffTableName} tariff,
            tariff_tmp
        SET
            tariff.service_type_id = tariff_tmp.service_type_id,
            tariff.currency_id = tariff_tmp.currency_id,
            tariff.name = tariff_tmp.name,
            tariff.tariff_status_id = tariff_tmp.tariff_status_id,
            tariff.is_include_vat = tariff_tmp.is_include_vat,
            tariff.tariff_person_id = tariff_tmp.tariff_person_id,
            tariff.country_id = tariff_tmp.country_id,
            tariff.is_autoprolongation = tariff_tmp.is_autoprolongation,
            tariff.is_charge_after_period = tariff_tmp.is_charge_after_period,
            tariff.is_charge_after_blocking = tariff_tmp.is_charge_after_blocking,
            tariff.count_of_validity_period = tariff_tmp.count_of_validity_period,
            tariff.insert_user_id = tariff_tmp.insert_user_id,
            tariff.insert_time = tariff_tmp.insert_time,
            tariff.update_user_id = tariff_tmp.update_user_id,
            tariff.update_time = tariff_tmp.update_time,
            tariff.voip_tarificate_id = tariff_tmp.voip_tarificate_id
        WHERE
            tariff.id = tariff_tmp.id
        ");
        printf('updated = %d, ', $affectedRows);

        // удалить обновленные из временной таблицы
        $this->execute("DELETE
            tariff_tmp.*
        FROM
            {$tariffTableName} tariff,
            tariff_tmp
        WHERE
            tariff.id = tariff_tmp.id
        ");

        // оставшие добавить
        $affectedRows = $this->execute("INSERT INTO {$tariffTableName}
            (id, service_type_id, currency_id, name, tariff_status_id, is_include_vat, tariff_person_id, country_id,
            is_autoprolongation, is_charge_after_period, is_charge_after_blocking, count_of_validity_period,
            insert_user_id, insert_time, update_user_id, update_time, voip_tarificate_id)
        SELECT *
        FROM tariff_tmp
        ");
        printf('added = %d', $affectedRows);

        // убрать за собой
        $this->execute("DROP TABLE tariff_tmp");
    }

    /**
     * Доконвертировать TariffPeriod
     */
    protected function convertTariffPeriod()
    {
        // Создать временную таблицу для конвертации периодов тарифа
        $this->createTemporaryTableForTariffPeriod();

        $tariffPeriodTableName = TariffPeriod::tableName();

        // Обновить ранее сконвертированные. Это можно по tariff_id, потому что в старых тарифах период всегда один
        $affectedRows = $this->execute("UPDATE
            {$tariffPeriodTableName} tariff_period,
            tariff_period_tmp
        SET
            tariff_period.price_per_period = tariff_period_tmp.price_per_period,
            tariff_period.price_setup = tariff_period_tmp.price_setup,
            tariff_period.price_min = tariff_period_tmp.price_min,
            tariff_period.period_id = tariff_period_tmp.period_id,
            tariff_period.charge_period_id = tariff_period_tmp.charge_period_id
        WHERE
            tariff_period.tariff_id = tariff_period_tmp.tariff_id
        ");
        printf('updated = %d, ', $affectedRows);

        // Удалить обновленные
        $this->execute("DELETE
            tariff_period_tmp.*
        FROM
            tariff_period_tmp,
            {$tariffPeriodTableName} tariff_period
        WHERE
            tariff_period.tariff_id = tariff_period_tmp.tariff_id
        ");

        // Оставшиеся - добавить
        $affectedRows = $this->execute("INSERT INTO {$tariffPeriodTableName}
          (price_per_period, price_setup, price_min, tariff_id, period_id, charge_period_id)
        SELECT *
        FROM tariff_period_tmp");
        printf('added = %d', $affectedRows);

        // убрать за собой
        $this->execute("DROP TABLE tariff_period_tmp");
    }

    /**
     * Создать временную таблицу для конвертации ресурсов тарифа
     */
    protected function createTemporaryTableForTariffResource()
    {
        return false;
    }

    /**
     * Доконвертировать TariffResource
     */
    protected function convertTariffResource()
    {
        // Создать временную таблицу для конвертации ресурсов тарифа
        if (!$this->createTemporaryTableForTariffResource()) {
            return;
        }

        $tariffResourceTableName = TariffResource::tableName();

        // Обновить ранее сконвертированные
        $affectedRows = $this->execute("UPDATE
            {$tariffResourceTableName} tariff_resource,
            tariff_resource_tmp
        SET
            tariff_resource.amount = tariff_resource_tmp.amount,
            tariff_resource.price_per_unit = tariff_resource_tmp.price_per_unit,
            tariff_resource.price_min = tariff_resource_tmp.price_min
        WHERE
            tariff_resource.tariff_id = tariff_resource_tmp.tariff_id
            AND tariff_resource.resource_id = tariff_resource_tmp.resource_id
        ");
        printf('updated = %d, ', $affectedRows);

        // Удалить обновленные
        $this->execute("DELETE
            tariff_resource_tmp.*
        FROM
            tariff_resource_tmp,
            {$tariffResourceTableName} tariff_resource
        WHERE
            tariff_resource.tariff_id = tariff_resource_tmp.tariff_id
            AND tariff_resource.resource_id = tariff_resource_tmp.resource_id
        ");

        // Оставшиеся - добавить
        $affectedRows = $this->execute("INSERT INTO {$tariffResourceTableName}
          (amount, price_per_unit, price_min, resource_id, tariff_id)
        SELECT *
        FROM tariff_resource_tmp");
        printf('added = %d', $affectedRows);

        // убрать за собой
        $this->execute("DROP TABLE tariff_resource_tmp");
    }

    /**
     * Постобработка
     */
    protected function postProcessing()
    {
    }
}
