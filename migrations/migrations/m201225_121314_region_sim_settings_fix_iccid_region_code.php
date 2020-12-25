<?php

use app\classes\Migration;
use app\modules\sim\models\RegionSettings;

/**
 * Class m201225_121314_region_sim_settings_fix_iccid_region_code
 */
class m201225_121314_region_sim_settings_fix_iccid_region_code extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $tableName = RegionSettings::tableName();

        $this->alterColumn($tableName, 'iccid_region_code', $this->string(8)->notNull());

        $sql = "UPDATE nispd.{$tableName} SET iccid_region_code = CONCAT('0', iccid_region_code) WHERE iccid_region_code < 10;";
        $this->execute($sql);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $tableName = RegionSettings::tableName();

        $this->alterColumn($tableName, 'iccid_region_code', $this->smallInteger()->notNull());
    }
}
