<?php

use app\classes\uu\model\AccountTariff;

class m160817_114429_alter_uu_account_tariff extends \app\classes\Migration
{
    public function up()
    {
        $tableName = AccountTariff::tableName();
        $fieldName = 'comment';
        $this->alterColumn($tableName, $fieldName, $this->text());
    }

    public function down()
    {
        $tableName = AccountTariff::tableName();
        $fieldName = 'comment';
        $this->alterColumn($tableName, $fieldName, $this->text()->notNull());
    }
}