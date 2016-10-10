<?php

namespace app\classes\uu\tarificator;

use app\classes\Connection;
use app\classes\Event;
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
            $accountTariffId
        );

        // проводки за абоненскую плату
        echo PHP_EOL . 'Проводки за абоненскую плату';
        $this->_tarificate(
            AccountLogPeriod::tableName(),
            new Expression((string)AccountEntry::TYPE_ID_PERIOD),
            'date_from',
            $accountTariffId,
            $sqlAndWhere = 'AND IF(client_account.is_postpaid > 0, account_log.date_to > NOW(), true)'
        );

        // проводки за ресурсы
        echo PHP_EOL . 'Проводки за ресурсы';
        $this->_tarificate(
            AccountLogResource::tableName(),
            'tariff_resource_id',
            'date',
            $accountTariffId
        );

        // проводки за минимальную плату
        echo PHP_EOL . 'Проводки за минимальную плату';
        $this->_tarificate(
            AccountLogMin::tableName(),
            new Expression((string)AccountEntry::TYPE_ID_MIN),
            'date_to',
            $accountTariffId,
            $sqlAndWhere = 'AND account_log.date_to > NOW()'
        );

        // Расчёт НДС
        echo PHP_EOL . 'Расчёт НДС';
        $this->_tarificateVat($accountTariffId);

        // очищаем флаг
        $this->cleanUpdateFlag();

        // получаем accountId изменившихся проводок
        // и генерируем события об этом
        $this->makeUpdateEvents($this->getUpdatedAccountIds());

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
     */
    private function _tarificate($accountLogTableName, $typeId, $dateFieldName, $accountTariffId, $sqlAndWhere = '')
    {
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
        echo '. ';
        $insertSQL = <<<SQL
            INSERT INTO {$accountEntryTableName}
            (date, account_tariff_id, type_id, price)
                SELECT DISTINCT
                    DATE_FORMAT(account_log.`{$dateFieldName}`, "%Y-%m-01"),
                    account_log.account_tariff_id,
                    {$typeId},
                    0
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
                AND account_entry.date = DATE_FORMAT(account_log.`{$dateFieldName}`, "%Y-%m-01")
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


        // посчитать цену без НДС для юр.лиц
        echo '. ';
        $accountTariffTableName = AccountTariff::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();
        $tariffTableName = Tariff::tableName();
        $tariffResourceTableName = TariffResource::tableName();
        $resourceIdVoipCalls = Resource::ID_VOIP_CALLS; // стоимость звонков от низкоуровневого биллера уже приходит с НДС
        $updateSql = <<<SQL
        UPDATE
            (
            {$accountEntryTableName} account_entry,
            {$accountTariffTableName} account_tariff,
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
               ),
            account_entry.is_updated = 1
        WHERE
            account_entry.vat_rate IS NOT NULL
            AND account_entry.account_tariff_id = account_tariff.id
            AND account_tariff.tariff_period_id = tariff_period.id
            AND tariff_period.tariff_id = tariff.id
            {$sqlAndWhere}
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
            price_with_vat = price_without_vat * (100 + vat_rate) / 100,
            is_updated = 1
        WHERE
            price_without_vat IS NOT NULL
            {$sqlAndWhere}
SQL;
        $db->createCommand($updateSql)
            ->execute();
        unset($updateSql);
    }

    /**
     * Возвращает массив id ЛС, в которых были измененны проводки
     *
     * @return array
     */
    public function getUpdatedAccountIds()
    {
        $accountEntryTableName = AccountEntry::tableName();
        $accountTariffTableName = AccountTariff::tableName();

        $sql = <<<SQL
        SELECT 
            GROUP_CONCAT(DISTINCT account_tariff.client_account_id) AS ids
        FROM 
            {$accountEntryTableName} account_entry,
            {$accountTariffTableName} account_tariff
        WHERE 
          account_entry.is_updated = 1
          AND account_entry.account_tariff_id = account_tariff.id
SQL;

        $ids = Yii::$app->db
            ->createCommand($sql)
            ->queryOne();

        if ($ids && $ids['ids']) {
            return explode(',', $ids['ids']);
        }

        return [];
    }

    /**
     * Создает событие на обновление счетов, сделаных из проводок.
     *
     * @param int []
     */
    public function makeUpdateEvents(array $ids)
    {
        foreach ($ids as $id) {
            Event::go(Event::UU_TARIFICATE, ['account_id' => $id]);
        }
    }

    /**
     * Снимаем флаг-признак с проводок, что они обновлены
     */
    public function cleanUpdateFlag()
    {
        $accountEntryTableName = AccountEntry::tableName();

        $sql = <<<SQL
        UPDATE
            {$accountEntryTableName}
        SET 
            is_updated = 0
        WHERE
            is_updated = 1
SQL;

        Yii::$app->db
            ->createCommand($sql)
            ->execute();
    }
}
