<?php

use app\classes\uu\model\AccountEntry;

class m160504_202300_add_account_entry_vat extends \app\classes\Migration
{
    /**
     * Накатить
     */
    public function safeUp()
    {
        $tableName = AccountEntry::tableName();
        $this->addColumn($tableName, 'price_without_vat', $this->float());
        $this->addColumn($tableName, 'vat_rate', $this->integer());
        $this->addColumn($tableName, 'vat', $this->float());
        $this->addColumn($tableName, 'price_with_vat', $this->float());
    }

    /**
     * Откатить
     */
    public function safeDown()
    {
        $tableName = AccountEntry::tableName();
        $this->dropColumn($tableName, 'price_without_vat');
        $this->dropColumn($tableName, 'vat_rate');
        $this->dropColumn($tableName, 'vat');
        $this->dropColumn($tableName, 'price_with_vat');
    }
}