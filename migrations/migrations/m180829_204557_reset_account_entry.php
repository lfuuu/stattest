<?php

use app\modules\uu\models\AccountEntry;
use app\modules\uu\models\AccountLogPeriod;
use yii\db\Expression;

/**
 * Class m180829_204557_reset_account_entry
 */
class m180829_204557_reset_account_entry extends \app\classes\Migration
{
    /**
     * Up
     * @throws \yii\db\Exception
     */
    public function safeUp()
    {
        $accountEntryTableName = AccountEntry::tableName();
        $accountLogPeriodTableName = AccountLogPeriod::tableName();

        // найти проводки, привязанные к посуточным транзакциям абонентки
        $sql = <<<SQL
            CREATE TEMPORARY TABLE account_entry_tmp
            SELECT DISTINCT account_entry_id FROM {$accountLogPeriodTableName} WHERE date_from = date_to
SQL;
        AccountEntry::getDb()->createCommand($sql)->execute();

        // отвязать транзакции абонентки от проводок. Они будут привязаны заново в AccountEntryTarificator
        $this->update($accountLogPeriodTableName, ['account_entry_id' => null], new Expression('date_from = date_to'));

        // удалить проводки из п. 1. Они будут созданы заново в AccountEntryTarificator
        $sql = <<<SQL
            DELETE 
                account_entry.*
            FROM
                {$accountEntryTableName} account_entry,
                 account_entry_tmp
            WHERE
                account_entry.id = account_entry_tmp.account_entry_id 
SQL;
        AccountEntry::getDb()->createCommand($sql)->execute();
    }

    /**
     * Down
     */
    public function safeDown()
    {
        // создаются заново автоматически в AccountEntryTarificator
    }
}
