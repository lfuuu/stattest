<?php

/**
 * Class m180418_115528_drop_sim
 */
class m180418_115528_drop_sim extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->dropTable('sim_imsi');
        $this->dropTable('sim_imsi_status');
        $this->dropTable('sim_card');
        $this->dropTable('sim_card_status');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        return false;
    }
}
