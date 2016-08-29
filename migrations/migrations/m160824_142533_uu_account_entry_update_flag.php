<?php

use app\classes\uu\model\AccountEntry;

class m160824_142533_uu_account_entry_update_flag extends \app\classes\Migration
{
    private $field = 'is_updated';

    public function up()
    {
        $table = AccountEntry::tableName();
        $this->addColumn($table, $this->field, $this->integer(1)->notNull()->defaultValue(0));
        $this->createIndex($table . '_' . $this->field, $table, $this->field);
    }

    public function down()
    {
        $table = AccountEntry::tableName();
        $this->dropIndex($table . '_' . $this->field, $table);
        $this->dropColumn($table, $this->field);
    }
}