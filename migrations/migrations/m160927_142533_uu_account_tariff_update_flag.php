<?php

use app\classes\uu\model\AccountTariff;

class m160927_142533_uu_account_tariff_update_flag extends \app\classes\Migration
{
    private $fieldIsUpdated = 'is_updated';

    public function up()
    {
        $table = AccountTariff::tableName();
        $this->dropColumn($table, $this->fieldIsUpdated);
    }

    public function down()
    {
        $table = AccountTariff::tableName();
        $this->addColumn($table, $this->fieldIsUpdated, $this->integer()->notNull()->defaultValue(0));
        $this->createIndex($table . '_' . $this->fieldIsUpdated, $table, $this->fieldIsUpdated);
    }
}