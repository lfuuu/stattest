<?php

use app\models\Bill;
use app\models\BillExternal;

/**
 * Class m180927_135011_bill_ext
 */
class m180927_135011_bill_ext extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $table = BillExternal::tableName();
        $billTable = Bill::tableName();
        $this->createTable($table, [
            'bill_no' => "varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL",
            'ext_bill_no' => $this->string(),
            'ext_bill_date' => $this->string(),
            'ext_invoice_no' => $this->string(),
            'ext_akt_no' => $this->string(),
            'ext_akt_date' => $this->string(),
        ]);

        $this->addPrimaryKey('pk-'.$table.'-bill_no', $table, 'bill_no');

        $this->addForeignKey('fk-'.$table.'-'. $billTable.'-bill_no', $table, 'bill_no', $billTable, 'bill_no', 'CASCADE', 'CASCADE');

        $this->createIndex('idx-'.$table.'-ext_bill_no', $table, 'ext_bill_no');
        $this->createIndex('idx-'.$table.'-ext_akt_no', $table, 'ext_akt_no');
        $this->createIndex('idx-'.$table.'-ext_invoice_no', $table, 'ext_invoice_no');

        $this->execute("insert into {$table} SELECT bill_no, bill_no_ext, if(bill_no_ext_date='0000-00-00', null, bill_no_ext_date), invoice_no_ext, null, null FROM `{$billTable}` where bill_no_ext");

        $this->dropColumn(Bill::tableName(), 'bill_no_ext');
        $this->dropColumn(Bill::tableName(), 'bill_no_ext_date');
        $this->dropColumn(Bill::tableName(), 'invoice_no_ext');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(BillExternal::tableName());
    }
}
