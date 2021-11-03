<?php

/**
 * Class m211103_154850_usage_cpe_tech_monitor
 */
class m211103_154850_usage_cpe_tech_monitor extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn('usage_tech_cpe', 'cacti_monitor_url', $this->string(1024)->notNull()->defaultValue(''));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn('usage_tech_cpe', 'cacti_monitor_url');
    }
}
