<?php

use app\models\Number;
use app\models\voip\Registry;

/**
 * Class m200316_165042_number_status_released_and_ported
 */
class m200316_165042_number_status_released_and_ported extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(Number::tableName(), 'status', 'enum(\'notsale\',\'instock\',\'active_tested\',\'active_commercial\',\'notactive_reserved\',\'notactive_hold\',\'released\',\'released_and_ported\',\'active_connected\') NOT NULL DEFAULT \'notsale\'');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(Number::tableName(), 'status', 'enum(\'notsale\',\'instock\',\'active_tested\',\'active_commercial\',\'notactive_reserved\',\'notactive_hold\',\'released\',\'active_connected\') NOT NULL DEFAULT \'notsale\'');
    }
}
