<?php

use app\modules\uu\models\AccountTariff;

/**
 * Class m171111_153739_add_uu_multitrunk
 */
class m171111_153739_add_uu_multitrunk extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(AccountTariff::tableName(), 'trunk_type_id', $this->integer());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(AccountTariff::tableName(), 'trunk_type_id');
    }
}
