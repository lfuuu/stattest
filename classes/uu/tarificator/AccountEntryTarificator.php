<?php

namespace app\classes\uu\tarificator;

use app\classes\Connection;
use app\classes\uu\model\AccountEntry;
use app\classes\uu\model\AccountLogMin;
use app\classes\uu\model\AccountLogPeriod;
use app\classes\uu\model\AccountLogResource;
use app\classes\uu\model\AccountLogSetup;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\Resource;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPeriod;
use app\classes\uu\model\TariffResource;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\Organization;
use Yii;
use yii\db\Expression;

/**
 * Расчет для бухгалтерской проводки (AccountEntry)
 *
 * @link http://bugtracker.welltime.ru/jira/browse/BIL-1909
 * Счет на postpaid никогда не создается
 * При подключении новой услуги prepaid сразу же создается счет на эту услугу. Если в течение календарных суток подключается вторая услуга, то она добавляется в первый счет.
 *      Если в новые календарные сутки - создается новый счет. В этот счет идет подключение подключение и абонентка. Ресурсы и минималка никогда сюда не попадают.
 * 1го числа каждого месяца создается новый счет за все prepaid абонентки, не вошедшие в отдельные счета (то есть абонентки автопродлеваемых услуг), все ресурсы и минималки.
 *      Подключение в этот счет не должно попасть.
 * Из любого счета всегда исключаются строки с нулевой стоимостью. Если в счете нет ни одной строки - он автоматически удаляется.
 *
 * Иными словами можно сказать:
 * проводки за подключение группируются посуточно и на их основе создаются счета. В эти же счета добавляются проводки за абонентку от этих же услуг за эту же дату
 * все остальные проводки группируются помесячно и на их основе создаются счета.
 */
class AccountEntryTarificator implements TarificatorI
{
    /**
     * На основе новых транзакций создать новые проводки или добавить в существующие
     *
     * @param int|null $accountTariffId Если указан, то только для этой услуги. Если не указан - для всех
     */
    public function tarificate($accountTariffId = null)
    {
        // проводки за подключение
        echo 'Проводки за подключение';
        $this->_tarificate(
            AccountLogSetup::tableName(),
            new Expression((string)AccountEntry::TYPE_ID_SETUP),
            'date',
            $accountTariffId,
            $sqlAndWhere = '',
            $isDefault = 0
        );

        // проводки за абоненскую плату
        // сначала с !$isDefault, потом с $isDefault
        echo PHP_EOL . 'Проводки за абоненскую плату';
        $this->_tarificate(
            AccountLogPeriod::tableName(),
            new Expression((string)AccountEntry::TYPE_ID_PERIOD),
            'date_from',
            $accountTariffId,
            $sqlAndWhere = '',
            $isDefault = 0
        );
        $this->_tarificate(
            AccountLogPeriod::tableName(),
            new Expression((string)AccountEntry::TYPE_ID_PERIOD),
            'date_from',
            $accountTariffId,
            $sqlAndWhere = 'AND IF(client_account.is_postpaid > 0, account_log.date_to > NOW(), true)',
            $isDefault = 1
        );

        // проводки за ресурсы
        echo PHP_EOL . 'Проводки за ресурсы';
        $this->_tarificate(
            AccountLogResource::tableName(),
            'tariff_resource_id',
            'date',
            $accountTariffId,
            $sqlAndWhere = '',
            $isDefault = 1
        );

        // проводки за минимальную плату
        echo PHP_EOL . 'Проводки за минимальную плату';
        $this->_tarificate(
            AccountLogMin::tableName(),
            new Expression((string)AccountEntry::TYPE_ID_MIN),
            'date_to',
            $accountTariffId,
            $sqlAndWhere = 'AND account_log.date_to < DATE_FORMAT(NOW(), "%Y-%m-%d")',
            $isDefault = 1
        );

        // Расчёт НДС
        echo PHP_EOL . 'Расчёт НДС';
        $this->_tarificateVat($accountTariffId);

        echo PHP_EOL;
    }

    /**
     * На основе новых транзакций создать проводки
     *
     * @param string $accountLogTableName
     * @param int|Expression $typeId
     * @param string $dateFieldName
     * @param int|null $accountTariffId Если указан, то только для этой услуги. Если не указан - для всех
     * @param string $sqlAndWhere
     * @param int $isDefault
     * @param string $sqlAndWhere
     */
    private function _tarificate($accountLogTableName, $typeId, $dateFieldName, $accountTariffId, $sqlAndWhere, $isDefault)
    {
        $dateFormat = $isDefault ? '%Y-%m-01' : '%Y-%m-%d';

        /** @var Connection $db */
        $db = Yii::$app->db;
        $accountEntryTableName = AccountEntry::tableName();
        $accountTariffTableName = AccountTariff::tableName();
        $clientAccountTableName = ClientAccount::tableName();
        $sqlParams = [];

        if ($accountTariffId) {
            $sqlAndWhere .= ' AND account_log.account_tariff_id = :account_tariff_id';
            $sqlParams[':account_tariff_id'] = $accountTariffId;
        }

        // создать пустые проводки
        // проводки за подключение датируются своей датой
        //      за абонентку - если есть проводки за подключение, то своей датой. Иначе 01
        //      за ресурсы и минималку - 01
        echo '. ';
        $insertSQL = <<<SQL
            INSERT INTO {$accountEntryTableName}
            (date, account_tariff_id, type_id, price, is_default)
                SELECT DISTINCT
                    DATE_FORMAT(account_log.`{$dateFieldName}`, "{$dateFormat}"),
                    account_log.account_tariff_id,
                    {$typeId},
                    0,
                    {$isDefault}
                FROM
                    {$accountLogTableName} account_log,
                    {$accountTariffTableName} account_tariff,
                    {$clientAccountTableName} client_account
                WHERE
                    account_log.account_entry_id IS NULL
                    AND account_log.account_tariff_id = account_tariff.id
                    AND account_tariff.client_account_id = client_account.id
                    {$sqlAndWhere}
            ON DUPLICATE KEY UPDATE price = 0
SQL;
        $db->createCommand($insertSQL, $sqlParams)
            ->execute();
        unset($insertSQL);

        if (!$isDefault && $accountLogTableName == AccountLogPeriod::tableName()) {
            // костыль для абонентки
            // если есть соотвествующая проводка за подключение, то должна быть и за абонентку.
            // а все остальные абонентки включать в базовую (isDefault) проводку
            // поэтому создаем проводки для всех абоненток, а потом удаляем ненужные (для которых нет соотвествующих проводок за подключение). Это гораздо проще, чем сразу создавать только нужные
            $accountEntryTypeIdSetup = AccountEntry::TYPE_ID_SETUP;
            $accountEntryTypeIdPeriod = AccountEntry::TYPE_ID_PERIOD;
            $deleteSQL = <<<SQL
            DELETE
                account_entry_period
            FROM
                {$accountEntryTableName} account_entry_period
            LEFT JOIN
                {$accountEntryTableName} account_entry_setup
                ON account_entry_setup.type_id = {$accountEntryTypeIdSetup}
                AND account_entry_setup.is_default = 0
                AND account_entry_setup.date = account_entry_period.date
                AND account_entry_setup.account_tariff_id = account_entry_period.account_tariff_id
            WHERE
                account_entry_period.type_id = {$accountEntryTypeIdPeriod}
                AND account_entry_period.is_default = 0
                AND account_entry_setup.id IS NULL
SQL;
            $db->createCommand($deleteSQL, $sqlParams)
                ->execute();
            unset($deleteSQL);

        }

        // привязать транзакции к проводкам
        echo '. ';
        $updateSql = <<<SQL
            UPDATE
                {$accountEntryTableName} account_entry,
                {$accountLogTableName} account_log,
                {$accountTariffTableName} account_tariff,
                {$clientAccountTableName} client_account
            SET
                account_log.account_entry_id = account_entry.id
            WHERE
                account_log.account_entry_id IS NULL
                AND account_entry.is_default = {$isDefault}
                AND account_entry.date = DATE_FORMAT(account_log.`{$dateFieldName}`, "{$dateFormat}")
                AND account_entry.type_id = {$typeId}
                AND account_entry.account_tariff_id = account_log.account_tariff_id
                AND account_log.account_tariff_id = account_tariff.id
                AND account_tariff.client_account_id = client_account.id
                {$sqlAndWhere}
SQL;
        $db->createCommand($updateSql, $sqlParams)
            ->execute();
        unset($updateSql);

        // пересчитать стоимость проводок
        echo '. ';
        $updateSql = <<<SQL
            UPDATE
            {$accountEntryTableName} account_entry,
            (
                SELECT
                    account_log.account_entry_id,
                    SUM(account_log.price) AS price
                FROM
                    {$accountLogTableName} account_log,
                    {$accountTariffTableName} account_tariff,
                    {$clientAccountTableName} client_account
                WHERE
                    account_log.account_tariff_id = account_tariff.id
                    AND account_tariff.client_account_id = client_account.id
                    {$sqlAndWhere}
                GROUP BY
                   account_log.account_entry_id
            ) t
         SET
            account_entry.price = t.price
         WHERE
            account_entry.id = t.account_entry_id
SQL;
        $db->createCommand($updateSql, $sqlParams)
            ->execute();
        unset($updateSql);
    }

    /**
     * Посчитать НДС
     * Проще через ClientAccount->getOrganization()->vat_rate, но это слишком долго. Поэтому хардкор
     *
     * @param int|null $accountTariffId Если указан, то только для этой услуги. Если не указан - для всех
     */
    private function _tarificateVat($accountTariffId)
    {
        /** @var Connection $db */
        $db = Yii::$app->db;
        $accountEntryTableName = AccountEntry::tableName();

        if ($accountTariffId) {
            $sqlAndWhere = ' AND account_entry.account_tariff_id = ' . $accountTariffId;
        } else {
            $sqlAndWhere = '';
        }

        // посчитать ставку НДС для юр.лиц
        echo '. ';
        $accountTariffTableName = AccountTariff::tableName();
        $clientAccountTableName = ClientAccount::tableName();
        $clientContractTableName = ClientContract::tableName();
        $organizationTableName = Organization::tableName();
        $updateSql = <<<SQL
        UPDATE
            {$accountEntryTableName} account_entry,
            {$accountTariffTableName} account_tariff,
            {$clientAccountTableName} client_account,
            {$clientContractTableName} client_contract,
            {$organizationTableName} organization
        SET
            account_entry.vat_rate = organization.vat_rate
        WHERE
            account_entry.account_tariff_id = account_tariff.id
            AND account_tariff.client_account_id = client_account.id
            AND client_account.contract_id = client_contract.id
            AND client_contract.organization_id = organization.organization_id
            AND organization.actual_from <= account_entry.date
            AND account_entry.date < organization.actual_to
            {$sqlAndWhere}
SQL;
        $db->createCommand($updateSql)
            ->execute();
        unset($updateSql);


        // посчитать цену без НДС
        $accountTariffTableName = AccountTariff::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();
        $tariffTableName = Tariff::tableName();
        $tariffResourceTableName = TariffResource::tableName();
        $resourceIdVoipCalls = Resource::ID_VOIP_CALLS; // стоимость звонков от низкоуровневого биллера уже приходит с НДС

        // нужно знать is_include_vat из тарифа, а это можно получить только через транзакции
        // @todo может быть несколько транзакций на одну проводку. Будет лишнее обновление, но на значения это не влияет
        $accountLogs = [
            AccountLogSetup::tableName() => 'account_entry.type_id = ' . AccountEntry::TYPE_ID_SETUP,
            AccountLogPeriod::tableName() => 'account_entry.type_id = ' . AccountEntry::TYPE_ID_PERIOD,
            AccountLogMin::tableName() => 'account_entry.type_id = ' . AccountEntry::TYPE_ID_MIN,
            AccountLogResource::tableName() => 'account_entry.type_id > 0',
        ];
        foreach($accountLogs as $accountLogTableName => $sqlAndWhereTmp) {
            echo '. ';
            $updateSql = <<<SQL
        UPDATE
            (
            {$accountEntryTableName} account_entry,
            {$accountLogTableName} account_log,
            {$tariffPeriodTableName} tariff_period,
            {$tariffTableName} tariff
            )
        LEFT JOIN
            {$tariffResourceTableName} tariff_resource
            ON account_entry.type_id = tariff_resource.id
        SET
            account_entry.price_without_vat = IF(
                (account_entry.type_id < 0 AND tariff.is_include_vat) OR (tariff_resource.resource_id IS NOT NULL AND tariff_resource.resource_id = {$resourceIdVoipCalls}),
                account_entry.price * 100 / (100 + account_entry.vat_rate),
                account_entry.price
               )
        WHERE
            account_entry.vat_rate IS NOT NULL
            AND account_entry.id = account_log.account_entry_id
            AND account_log.tariff_period_id = tariff_period.id
            AND tariff_period.tariff_id = tariff.id
            {$sqlAndWhere}
SQL;
            $db->createCommand($updateSql)
                ->execute();
            unset($updateSql);
        }


        // посчитать НДС и цену с НДС для юр.лиц
        echo '. ';
        $updateSql = <<<SQL
        UPDATE
            {$accountEntryTableName} account_entry
        SET
            vat = price_without_vat * vat_rate / 100,
            price_with_vat = price_without_vat * (100 + vat_rate) / 100
        WHERE
            price_without_vat IS NOT NULL
            {$sqlAndWhere}
SQL;
        $db->createCommand($updateSql)
            ->execute();
        unset($updateSql);
    }
}
