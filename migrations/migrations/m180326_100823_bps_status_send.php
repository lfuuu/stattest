<?php

use app\models\BusinessProcessStatus;

/**
 * Class m180326_100823_bps_status_send
 */
class m180326_100823_bps_status_send extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(BusinessProcessStatus::tableName(), 'is_bill_send', $this->boolean()->notNull()->defaultValue(false));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(BusinessProcessStatus::tableName(), 'is_bill_send');
    }
}
