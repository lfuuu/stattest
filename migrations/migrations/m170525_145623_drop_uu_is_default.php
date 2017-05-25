<?php

use app\models\ClientAccount;
use app\modules\uu\models\AccountEntry;
use app\modules\uu\models\AccountLogMin;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountLogResource;
use app\modules\uu\models\AccountLogSetup;
use app\modules\uu\models\Bill;

/**
 * Handles the dropping for table `uu_is_default`.
 */
class m170525_145623_drop_uu_is_default extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->_clear();

        $accountEntryTableName = AccountEntry::tableName();
        $this->dropColumn($accountEntryTableName, 'is_default');
        $this->addColumn($accountEntryTableName, 'is_next_month', $this->integer()->notNull());

        $billTableName = Bill::tableName();
        $this->dropColumn($billTableName, 'is_default');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->_clear();

        $accountEntryTableName = AccountEntry::tableName();
        $this->addColumn($accountEntryTableName, 'is_default', $this->integer()->notNull());
        $this->dropColumn($accountEntryTableName, 'is_next_month');

        $billTableName = Bill::tableName();
        $this->addColumn($billTableName, 'is_default', $this->integer()->notNull());
    }

    /**
     * Очистить Entry
     */
    private function _clear()
    {
        AccountLogSetup::updateAll(['account_entry_id' => null]);
        AccountLogPeriod::updateAll(['account_entry_id' => null]);
        AccountLogResource::updateAll(['account_entry_id' => null]);
        AccountLogMin::updateAll(['account_entry_id' => null]);
        AccountEntry::deleteAll();

        // сначала надо удалить старые сконвертированные счета, иначе они останутся, потому что у них 'on delete set null'
        \app\models\Bill::deleteAll([
            'AND',
            ['biller_version' => ClientAccount::VERSION_BILLER_UNIVERSAL],
            ['IS NOT', 'uu_bill_id', null]
        ]);

        AccountEntry::updateAll(['bill_id' => null]);
        Bill::deleteAll();
    }
}
