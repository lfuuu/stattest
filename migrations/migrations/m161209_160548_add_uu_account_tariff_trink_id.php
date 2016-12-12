<?php

use app\classes\uu\model\AccountTariff;

class m161209_160548_add_uu_account_tariff_trink_id extends \app\classes\Migration
{
    public function up()
    {
        $tableName = AccountTariff::tableName();
        $this->addColumn($tableName, 'trunk_id', $this->integer());
    }

    public function down()
    {
        $tableName = AccountTariff::tableName();
        $this->dropColumn($tableName, 'trunk_id');
    }
}