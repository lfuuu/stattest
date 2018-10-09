<?php

use app\models\BillExternal;

/**
 * Class m181009_110020_bill_ext_vat
 */
class m181009_110020_bill_ext_vat extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(BillExternal::tableName(), 'ext_vat', $this->decimal(12,2));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(BillExternal::tableName(), 'ext_vat');
    }
}
