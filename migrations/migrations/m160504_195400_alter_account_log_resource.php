<?php

use app\classes\uu\model\AccountLogResource;

class m160504_195400_alter_account_log_resource extends \app\classes\Migration
{
    /**
     * Накатить
     */
    public function safeUp()
    {
        $tableName = AccountLogResource::tableName();

        $fieldName = 'amount_use';
        $this->alterColumn($tableName, $fieldName, $this->float()->notNull());

        $fieldName = 'amount_overhead';
        $this->alterColumn($tableName, $fieldName, $this->float()->notNull());

        $fieldName = 'price';
        $this->alterColumn($tableName, $fieldName, $this->float()->notNull());
    }

    /**
     * Откатить
     */
    public function safeDown()
    {
        $tableName = AccountLogResource::tableName();

        $fieldName = 'amount_use';
        $this->alterColumn($tableName, $fieldName, $this->float());

        $fieldName = 'amount_overhead';
        $this->alterColumn($tableName, $fieldName, $this->integer());

        $fieldName = 'price';
        $this->alterColumn($tableName, $fieldName, $this->float());
    }
}