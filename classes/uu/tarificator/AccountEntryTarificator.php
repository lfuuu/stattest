<?php

namespace app\classes\uu\tarificator;

use app\classes\uu\model\AccountEntry;
use app\classes\uu\model\AccountLogPeriod;
use app\classes\uu\model\AccountLogResource;
use app\classes\uu\model\AccountLogSetup;
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

        // проводки за ресурсы (каждый по-отдельности)
        echo PHP_EOL . 'Проводки за ресурсы (каждый по-отдельности)';
        $this->_tarificateAll(
            AccountLogResource::tableName(),
            'tariff_resource_id',
            '`date`'
        );

        echo PHP_EOL;
    }

    /**
     * На основе новых транзакций создать новые пустые проводки
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
}
