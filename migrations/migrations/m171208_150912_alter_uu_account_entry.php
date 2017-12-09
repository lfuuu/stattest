<?php

use app\modules\uu\models\AccountEntry;

/**
 * Class m171208_150912_alter_uu_account_entry
 */
class m171208_150912_alter_uu_account_entry extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        // добавил поле is_next_month в unique
        $tableName = AccountEntry::tableName();
        $indexName = 'uniq-date-type_id-account_tariff_id-tariff_period_id';
        $this->dropIndex($indexName, $tableName);
        $this->createIndex($indexName, $tableName, ['date', 'type_id', 'account_tariff_id', 'tariff_period_id', 'is_next_month'], $unique = true);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $tableName = AccountEntry::tableName();
        $indexName = 'uniq-date-type_id-account_tariff_id-tariff_period_id';
        $this->dropIndex($indexName, $tableName);
        $this->createIndex($indexName, $tableName, ['date', 'type_id', 'account_tariff_id', 'tariff_period_id'], $unique = true);
    }
}
