<?php

use app\classes\Migration;
use app\modules\sim\models\RegionSettings;

/**
 * Class m210127_171819_region_sim_settings_last_used_imsi
 */
class m210127_171819_region_sim_settings_last_used_imsi extends Migration
{
    public $tableName;

    /**
     * Up
     */
    public function safeUp()
    {
        $this->tableName = RegionSettings::tableName();

        $this->addColumn(
            $this->tableName,
            'imsi_last_used',
            $this
                ->integer()
                ->notNull()
                ->defaultValue(0)
                ->after('imsi_range_length')
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'imsi_last_used');
    }
}
