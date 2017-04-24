<?php
use app\models\Number;

/**
 * Class m170420_175014_voip_number_status_connected
 */
class m170420_175014_voip_number_status_connected extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(app\models\Number::tableName(), 'status',
            "enum('notsale','instock','active_tested','active_commercial','notactive_reserved','notactive_hold','released','" . Number::STATUS_ACTIVE_CONNECTED . "') NOT NULL DEFAULT 'notsale'");
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(app\models\Number::tableName(), 'status',
            "enum('notsale','instock','active_tested','active_commercial','notactive_reserved','notactive_hold','released') NOT NULL DEFAULT 'notsale'");
    }
}
