<?php

class m161223_093620_important_events_remove_outdated extends \app\classes\Migration
{

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->dropTable('important_events_rules_conditions');
        $this->dropTable('important_events_rules');
        $this->dropTable('important_events_properties');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
    }

}