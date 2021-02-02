<?php

use app\classes\Migration;
use app\models\DidGroup;

/**
 * Class m210202_164155_did_group_statuses
 */
class m210202_164155_did_group_statuses extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        for ($i = 19; $i <= 24; $i++) {
            $this->addColumn(DidGroup::tableName(), 'price' . $i, $this->integer());
            $this->addColumn(DidGroup::tableName(), 'tariff_status_main' . $i, $this->integer());
            $this->addColumn(DidGroup::tableName(), 'tariff_status_package' . $i, $this->integer());
        }
    }

    /**
     * Down
     */
    public function safeDown()
    {
        for ($i = 24; $i >= 19; $i--) {
            $this->dropColumn(DidGroup::tableName(), 'price' . $i);
            $this->dropColumn(DidGroup::tableName(), 'tariff_status_main' . $i);
            $this->dropColumn(DidGroup::tableName(), 'tariff_status_package' . $i);
        }
    }
}
