<?php

namespace app\classes\uu\converter;

use app\classes\Connection;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPeriod;
use Yii;

/**
 */
abstract class AccountTariffConverterA
{
    /**
     * Создать временную таблицу для конвертации услуги
     */
    protected abstract function createTemporaryTableForAccountTariff();

    /**
     * Конвертировать лог тарифов
     * @return int
     */
    protected abstract function insertIntoAccountTariffLog();

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
        echo PHP_EOL . 'convertAccountTariff: ';
        $this->convertAccountTariff();

        echo PHP_EOL . 'convertAccountTariffLog: ';
        $this->convertAccountTariffLog($serviceTypeId);

        echo PHP_EOL . 'postProcessing: ';
        $this->postProcessing();

        echo PHP_EOL;
    }

    /**
     * Доконвертировать AccountTariff
     */
    protected function convertAccountTariff()
    {
        // Создать временную таблицу для конвертации услуги
        $this->createTemporaryTableForAccountTariff();

        $accountTariffTableName = AccountTariff::tableName();

        // обновить ранее конвертированные универсальные услуги
        $affectedRows = $this->execute("UPDATE
            {$accountTariffTableName} account_tariff,
            account_tariff_tmp
        SET
            account_tariff.client_account_id = account_tariff_tmp.client_account_id,
            account_tariff.service_type_id = account_tariff_tmp.service_type_id,
            account_tariff.region_id = account_tariff_tmp.region_id,
            account_tariff.prev_account_tariff_id = account_tariff_tmp.prev_account_tariff_id,
            account_tariff.comment = account_tariff_tmp.comment
        WHERE
            account_tariff.id = account_tariff_tmp.id
        ");
        printf('updated = %d, ', $affectedRows);

        // удалить обновленные из временной таблицы
        $this->execute("DELETE
            account_tariff_tmp.*
        FROM
            {$accountTariffTableName} account_tariff,
            account_tariff_tmp
        WHERE
            account_tariff.id = account_tariff_tmp.id
        ");

        // оставшие добавить
        $affectedRows = $this->execute("INSERT INTO {$accountTariffTableName}
            (id, client_account_id, service_type_id, region_id, prev_account_tariff_id, comment, voip_number)
        SELECT *
        FROM account_tariff_tmp
        ");
        printf('added = %d', $affectedRows);

        // убрать за собой
        $this->execute("DROP TABLE account_tariff_tmp");
    }

    /**
     * Доконвертировать AccountTariffLog
     * @param int $serviceTypeId
     */
    protected function convertAccountTariffLog($serviceTypeId)
    {
        $accountTariffTableName = AccountTariff::tableName();
        $accountTariffLogTableName = AccountTariffLog::tableName();
        $accountTariffDelta = AccountTariff::DELTA;

        // удалить старый лог тарифов
        $affectedRows = $this->execute("DELETE
            account_tariff_log.*
        FROM
            {$accountTariffTableName} account_tariff,
            {$accountTariffLogTableName} account_tariff_log
        WHERE
            account_tariff.id = account_tariff_log.account_tariff_id
            AND account_tariff.service_type_id = {$serviceTypeId}
            AND account_tariff.id < {$accountTariffDelta}
        ");
        printf('before = %d, ', $affectedRows);

        // Конвертировать лог тарифов
        $affectedRows = $this->insertIntoAccountTariffLog();
        printf('after = %d', $affectedRows);


        // после окончания услуги не должно быть никаких новых логов тарифа
        $this->execute("DELETE active.*
          FROM {$accountTariffLogTableName} active,
              {$accountTariffLogTableName} closed
          WHERE
            closed.tariff_period_id IS NULL
            AND closed.account_tariff_id = active.account_tariff_id
            AND active.actual_from > closed.actual_from
          ");

        $this->calcAccountTariffTariff($serviceTypeId);
    }

    /**
     * @todo кэш последнего тарифа
     * @param int $serviceTypeId
     */
    protected function calcAccountTariffTariff($serviceTypeId)
    {
        $accountTariffTableName = AccountTariff::tableName();
        $accountTariffLogTableName = AccountTariffLog::tableName();

        $this->execute("UPDATE {$accountTariffTableName}
        SET
          tariff_period_id = (
            SELECT {$accountTariffLogTableName}.tariff_period_id
            FROM {$accountTariffLogTableName}
            WHERE
              {$accountTariffTableName}.id = {$accountTariffLogTableName}.account_tariff_id
            ORDER BY
              {$accountTariffLogTableName}.actual_from DESC,
              {$accountTariffLogTableName}.id DESC
            LIMIT 1
          )
          WHERE
              service_type_id = {$serviceTypeId}
        ");
    }

    /**
     * Постобработка
     * Установить AccountTariff.ServiceTypeId на основе Tariff.ServiceTypeId
     */
    protected function postProcessing()
    {
        $accountTariffTableName = AccountTariff::tableName();
        $accountTariffLogTableName = AccountTariffLog::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();
        $tariffTableName = Tariff::tableName();

        // для текущего тарифа
        $this->execute("UPDATE 
            {$accountTariffTableName},
            {$tariffPeriodTableName},
            {$tariffTableName}
        SET
          {$accountTariffTableName}.service_type_id = {$tariffTableName}.service_type_id
        WHERE
          {$accountTariffTableName}.tariff_period_id = {$tariffPeriodTableName}.id
          AND {$tariffPeriodTableName}.tariff_id = {$tariffTableName}.id
        ");

        // для закрытого тарифа - по логу
        $this->execute("UPDATE 
            {$accountTariffTableName},
            {$accountTariffLogTableName},
            {$tariffPeriodTableName},
            {$tariffTableName}
        SET
          {$accountTariffTableName}.service_type_id = {$tariffTableName}.service_type_id
        WHERE
          {$accountTariffTableName}.tariff_period_id IS NULL
          AND {$accountTariffTableName}.id = {$accountTariffLogTableName}.account_tariff_id
          AND {$accountTariffLogTableName}.tariff_period_id = {$tariffPeriodTableName}.id
          AND {$tariffPeriodTableName}.tariff_id = {$tariffTableName}.id
        ");
    }
}