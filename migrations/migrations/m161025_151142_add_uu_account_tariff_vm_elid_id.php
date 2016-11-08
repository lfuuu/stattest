<?php

use app\classes\uu\model\AccountTariff;

class m161025_151142_add_uu_account_tariff_vm_elid_id extends \app\classes\Migration
{
    public function up()
    {
        $tableName = AccountTariff::tableName();
        $fieldName = 'vm_elid_id';
        $this->addColumn($tableName, $fieldName, $this->integer());
    }

    public function down()
    {
        $tableName = AccountTariff::tableName();
        $fieldName = 'vm_elid_id';
        $this->dropColumn($tableName, $fieldName);
    }
}