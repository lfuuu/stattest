<?php

use app\classes\Migration;
use app\models\Bill;
use app\models\ClientAccount;

class m160726_082642_bill_biller_flag extends Migration
{
    public function up()
    {
        $tableName = Bill::tableName();
        $this->addColumn($tableName, 'biller_version', $this->integer(1)->unsigned()->defaultValue(ClientAccount::VERSION_BILLER_USAGE));
        $this->addColumn($tableName, 'uu_bill_id', $this->integer(11)->defaultValue(null));
        $this->addForeignKey('fk-' . $tableName . '-uu_bill_id', $tableName, 'uu_bill_id', \app\classes\uu\model\Bill::tableName(), 'id', 'SET NULL');

        $tableName = \app\models\BillLine::tableName();
        $this->addColumn($tableName, 'uu_account_entry_id', $this->integer(11)->defaultValue(null));
        $this->addForeignKey('fk-' . $tableName . '-uu_account_entry_id', $tableName, 'uu_account_entry_id', \app\classes\uu\model\AccountEntry::tableName(), 'id', 'SET NULL');
    }

    public function down()
    {
        $tableName = Bill::tableName();
        $this->dropColumn($tableName, 'biller_version');
        $this->dropForeignKey('fk-' . $tableName . '-uu_bill_id', $tableName);
        $this->dropColumn($tableName, 'uu_bill_id');

        $tableName = \app\models\BillLine::tableName();
        $this->dropForeignKey('fk-' . $tableName . '-uu_account_entry_id');
        $this->dropColumn($tableName, 'uu_account_entry_id');
    }
}