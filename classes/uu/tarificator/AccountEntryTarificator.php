<?php

namespace app\classes\uu\tarificator;

use app\classes\uu\model\AccountEntry;
use app\classes\uu\model\AccountLogMin;
use app\classes\uu\model\AccountLogPeriod;
use app\classes\uu\model\AccountLogResource;
use app\classes\uu\model\AccountLogSetup;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPeriod;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\Organization;
use Yii;
use yii\db\Expression;

/**
 * Расчет для бухгалтерской проводки (AccountEntry)
 */
class AccountEntryTarificator
{
    /**
     * На основе новых транзакций создать новые проводки или добавить в существующие
     */
    public function tarificateAll()
    {
        // проводки за подключение
        echo 'Проводки за подключение';
        $this->_tarificateAll(
            AccountLogSetup::tableName(),
            new Expression((string)AccountEntry::TYPE_ID_SETUP),
            '`date`'
        );

        // проводки за абоненскую плату
        echo PHP_EOL . 'Проводки за абоненскую плату';
        $this->_tarificateAll(
            AccountLogPeriod::tableName(),
            new Expression((string)AccountEntry::TYPE_ID_PERIOD),
            'date_from'
        );

        // проводки за ресурсы
        echo PHP_EOL . 'Проводки за ресурсы';
        $this->_tarificateAll(
            AccountLogResource::tableName(),
            'tariff_resource_id',
            '`date`'
        );

        // проводки за минимальную плату
        echo PHP_EOL . 'Проводки за минимальную плату';
        $this->_tarificateAll(
            AccountLogMin::tableName(),
            new Expression((string)AccountEntry::TYPE_ID_MIN),
            'date_from'
        );

        // Расчёт НДС
        echo PHP_EOL . 'Расчёт НДС';
        $this->_tarificateVat();

        echo PHP_EOL;
    }

    /**
     * На основе новых транзакций создать проводки
     * @param string $accountLogTableName
     * @param int|Expression $typeId
     * @param string $dateFieldName
     */
    private function _tarificateAll($accountLogTableName, $typeId, $dateFieldName)
    {
        $db = Yii::$app->db;
        $accountEntryTableName = AccountEntry::tableName();

        // создать пустые проводки
        echo '. ';
        $insertSQL = <<<SQL
            INSERT INTO {$accountEntryTableName}
            (date, account_tariff_id, type_id, price)
                SELECT DISTINCT
                    DATE_FORMAT(account_log.{$dateFieldName}, "%Y-%m-01"),
                    account_log.account_tariff_id,
                    {$typeId},
                    0
                FROM
                    {$accountLogTableName} account_log
                WHERE
                    account_log.account_entry_id IS NULL
            ON DUPLICATE KEY UPDATE price = 0
SQL;
        $db->createCommand($insertSQL)
            ->execute();
        unset($insertSQL);

        // привязать транзакции к проводкам
        echo '. ';
        $updateSql = <<<SQL
            UPDATE
               {$accountEntryTableName} account_entry,
               {$accountLogTableName} account_log
            SET
               account_log.account_entry_id = account_entry.id
            WHERE
               account_log.account_entry_id IS NULL
               AND account_entry.date = DATE_FORMAT(account_log.{$dateFieldName}, "%Y-%m-01")
               AND account_entry.type_id = {$typeId}
               AND account_entry.account_tariff_id = account_log.account_tariff_id
SQL;
        $db->createCommand($updateSql)
            ->execute();
        unset($updateSql);

        // пересчитать стоимость проводок
        echo '. ';
        $updateSql = <<<SQL
            UPDATE
            {$accountEntryTableName} account_entry,
            (
                SELECT
                   account_entry_id,
                   SUM(price) AS price
                FROM
                   {$accountLogTableName}
                GROUP BY
                   account_entry_id
            ) t
         SET
            account_entry.price = t.price
         WHERE
            account_entry.id = t.account_entry_id
SQL;
        $db->createCommand($updateSql)
            ->execute();
        unset($updateSql);
    }

    /**
     * Посчитать НДС
     * Проще через ClientAccount->getOrganization()->vat_rate, но это слишком долго. Поэтому хардкор
     */
    private function _tarificateVat()
    {
        $db = Yii::$app->db;
        $accountEntryTableName = AccountEntry::tableName();

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
SQL;
        $db->createCommand($updateSql)
            ->execute();
        unset($updateSql);


        // посчитать цену без НДС для юр.лиц
        echo '. ';
        $accountTariffTableName = AccountTariff::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();
        $tariffTableName = Tariff::tableName();
        $updateSql = <<<SQL
            UPDATE
            {$accountEntryTableName} account_entry,
            {$accountTariffTableName} account_tariff,
            {$tariffPeriodTableName} tariff_period,
            {$tariffTableName} tariff
         SET
            account_entry.price_without_vat = IF(
                account_entry.type_id < 0  AND tariff.is_include_vat,
                account_entry.price * 100 / (100 + account_entry.vat_rate),
                account_entry.price
               )
         WHERE
            account_entry.vat_rate IS NOT NULL
            AND account_entry.account_tariff_id = account_tariff.id
            AND account_tariff.tariff_period_id = tariff_period.id
            AND tariff_period.tariff_id = tariff.id
SQL;
        $db->createCommand($updateSql)
            ->execute();
        unset($updateSql);


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
SQL;
        $db->createCommand($updateSql)
            ->execute();
        unset($updateSql);
    }
}
