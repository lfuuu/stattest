<?php
use app\modules\uu\models\AccountEntry;

/**
 * Class m170706_101014_alter_uu_account_entry
 */
class m170706_101014_alter_uu_account_entry extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $tableName = AccountEntry::tableName();
        $this->dropIndex('uniq-uu_account_entry-date-type_id-account_tariff_id', $tableName);
        $this->createIndex('uniq-date-type_id-account_tariff_id-tariff_period_id', $tableName, ['date', 'type_id', 'account_tariff_id', 'tariff_period_id'], $unique = true);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $tableName = AccountEntry::tableName();
        $this->dropIndex('uniq-date-type_id-account_tariff_id-tariff_period_id', $tableName);
        $this->createIndex('uniq-uu_account_entry-date-type_id-account_tariff_id', $tableName, ['date', 'type_id', 'account_tariff_id'], $unique = true);
    }
}
