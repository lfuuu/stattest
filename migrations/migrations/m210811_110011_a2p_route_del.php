<?php

/**
 * Class m210811_110011_a2p_route_del
 */
class m210811_110011_a2p_route_del extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->execute('DROP TABLE IF EXISTS a2p_route');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        // a.... do nothing
    }
}
