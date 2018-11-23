<?php

use app\models\BillExternal;

/**
 * Class m181122_170151_ext_sum_without_vat
 */
class m181122_170151_ext_sum_without_vat extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(BillExternal::tableName(), 'ext_sum_without_vat', $this->decimal(12,2));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(BillExternal::tableName(), 'ext_sum_without_vat');
    }
}
