<?php

use app\models\BillExternal;

/**
 * Class m190912_102738_add_registration_date_ext
 */
class m190912_102738_add_registration_date_ext extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(BillExternal::tableName(),'ext_registration_date', $this->string(255));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(BillExternal::tableName(),'ext_registration_date');
    }
}
