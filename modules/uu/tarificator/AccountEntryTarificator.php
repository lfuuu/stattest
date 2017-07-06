<?php

namespace app\modules\uu\tarificator;

use app\classes\Connection;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\Organization;
use app\modules\uu\models\AccountEntry;
use app\modules\uu\models\AccountLogMin;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountLogResource;
use app\modules\uu\models\AccountLogSetup;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\Resource;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffPeriod;
use app\modules\uu\models\TariffResource;
use Yii;
use yii\db\Expression;

/**
 * Расчет для бухгалтерской проводки (AccountEntry)
 *
 * @link http://bugtracker.welltime.ru/jira/browse/BIL-1909
 */
class AccountEntryTarificator extends Tarificator
{
    /**
     * На основе новых транзакций создать новые проводки или добавить в существующие
     *
     * @param int|null $accountTariffId Если указан, то только для этой услуги. Если не указан - для всех
     * @throws \yii\db\Exception
     */
    public function tarificate($accountTariffId = null)
    {
        // Подключение
        // Транзакции группировать в проводки следующего месяца
        $this->out('Проводки за подключение');
        $this->_tarificate(AccountLogSetup::tableName(), new Expression((string)AccountEntry::TYPE_ID_SETUP), 'date', 'date', $accountTariffId, $isSplitByMonths = 0);

        // Абонентская плата
        // Постоплатные: все транзакции группировать в проводки следующего месяца
        // Предоплатные: помесячные транзакции от 1го числа группировать в проводки того же месяца. Остальные транзакции (посуточные или не от 1го числа) группировать в проводки следующего месяца
        $this->out(PHP_EOL . 'Проводки за абоненскую плату');
        $this->_tarificate(AccountLogPeriod::tableName(), new Expression((string)AccountEntry::TYPE_ID_PERIOD), 'date_from', 'date_to', $accountTariffId, $isSplitByMonths = 1);

        // Ресурсы
        // (аналогично абонентке)
        $this->out(PHP_EOL . 'Проводки за ресурсы');
        $this->_tarificate(AccountLogResource::tableName(), 'tariff_resource_id', 'date_from', 'date_to', $accountTariffId, $isSplitByMonths = 1);

        // Минимальная плата
        // Транзакции группировать в проводки следующего месяца
        $this->out(PHP_EOL . 'Проводки за минимальную плату');
        $this->_tarificate(AccountLogMin::tableName(), new Expression((string)AccountEntry::TYPE_ID_MIN), 'date_from', 'date_to', $accountTariffId, $isSplitByMonths = 0);

        // Расчёт НДС
        $this->out(PHP_EOL . 'Расчёт НДС');
        $this->_tarificateVat($accountTariffId);

        $this->out(PHP_EOL);
    }

    /**
     * На основе новых транзакций создать проводки
     *
     * @param string $accountLogTableName
     * @param int|Expression $typeId
     * @param string $dateFieldNameFrom
     * @param string $dateFieldNameTo
     * @param int|null $accountTariffId Если указан, то только для этой услуги. Если не указан - для всех
     * @param int $isSplitByMonths делить ли на прошлый/будущий месяц
     * @throws \yii\db\Exception
     */
    private function _tarificate($accountLogTableName, $typeId, $dateFieldNameFrom, $dateFieldNameTo, $accountTariffId, $isSplitByMonths)
    {
        /** @var Connection $db */
        $db = Yii::$app->db;
        $accountEntryTableName = AccountEntry::tableName();
        $accountTariffTableName = AccountTariff::tableName();
        $clientAccountTableName = ClientAccount::tableName();
        $sqlParams = [];

        $sqlAndWhere = '';
        if ($accountTariffId) {
            $sqlAndWhere .= ' AND account_log.account_tariff_id = :account_tariff_id';
            $sqlParams[':account_tariff_id'] = $accountTariffId;
        }

        if ($isSplitByMonths) {
            $isNextMonthSql = "(DATE_FORMAT(account_log.`{$dateFieldNameFrom}`, '%d') != '01' OR account_log.`{$dateFieldNameTo}` = account_log.`{$dateFieldNameFrom}`)";
        } else {
            $isNextMonthSql = 'true';
        }

        // создать пустые проводки
        $this->out('. ');
        $insertSQL = <<<SQL
            INSERT INTO {$accountEntryTableName}
            (date, account_tariff_id, type_id, price, is_next_month, tariff_period_id, date_from, date_to)
                SELECT DISTINCT
                    DATE_FORMAT(account_log.`{$dateFieldNameFrom}`, "%Y-%m-01") AS date,
                    account_log.account_tariff_id,
                    {$typeId} as type_id,
                    0 AS price,
                    IF(client_account.is_postpaid = 1 OR {$isNextMonthSql}, 1, 0) AS is_next_month,
                    account_log.tariff_period_id,
                    account_log.`{$dateFieldNameFrom}` AS date_from,
                    account_log.`{$dateFieldNameTo}` AS date_to
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

        // привязать транзакции к проводкам
        $this->out('. ');
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
                AND account_entry.is_next_month = IF(client_account.is_postpaid = 1 OR {$isNextMonthSql}, 1, 0)
                AND account_entry.date = DATE_FORMAT(account_log.`{$dateFieldNameFrom}`, "%Y-%m-01")
                AND account_entry.type_id = {$typeId}
                AND account_entry.account_tariff_id = account_log.account_tariff_id
                AND account_entry.tariff_period_id = account_log.tariff_period_id
                AND account_log.account_tariff_id = account_tariff.id
                AND account_tariff.client_account_id = client_account.id
                {$sqlAndWhere}
SQL;
        $db->createCommand($updateSql, $sqlParams)
            ->execute();
        unset($updateSql);

        // пересчитать стоимость проводок
        $this->out('. ');
        $updateSql = <<<SQL
            UPDATE
            {$accountEntryTableName} account_entry,
            (
                SELECT
                    account_log.account_entry_id,
                    SUM(account_log.price) AS price,
                    MIN(`{$dateFieldNameFrom}`) AS date_from,
                    MAX(`{$dateFieldNameTo}`) AS date_to
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
            account_entry.price = t.price,
            account_entry.date_from = t.date_from,
            account_entry.date_to = t.date_to
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
     * @throws \yii\db\Exception
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
        $this->out('. ');
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
        $tariffPeriodTableName = TariffPeriod::tableName();
        $tariffTableName = Tariff::tableName();
        $tariffResourceTableName = TariffResource::tableName();
        $resourceIdVoipCalls = implode(', ', [Resource::ID_VOIP_CALLS, Resource::ID_TRUNK_CALLS]); // стоимость звонков от низкоуровневого биллера уже приходит с НДС

        // нужно знать is_include_vat из тарифа, а это можно получить только через транзакции
        // @todo может быть несколько транзакций на одну проводку. Будет лишнее обновление, но на значения это не влияет
        $accountLogs = [
            AccountLogSetup::tableName() => 'account_entry.type_id = ' . AccountEntry::TYPE_ID_SETUP,
            AccountLogPeriod::tableName() => 'account_entry.type_id = ' . AccountEntry::TYPE_ID_PERIOD,
            AccountLogMin::tableName() => 'account_entry.type_id = ' . AccountEntry::TYPE_ID_MIN,
            AccountLogResource::tableName() => 'account_entry.type_id > 0',
        ];
        foreach ($accountLogs as $accountLogTableName => $sqlAndWhereTmp) {
            $this->out('. ');
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
                tariff.is_include_vat OR (tariff_resource.resource_id IS NOT NULL AND tariff_resource.resource_id IN ({$resourceIdVoipCalls})),
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
        $this->out('. ');
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
