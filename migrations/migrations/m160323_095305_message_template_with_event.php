<?php

class m160323_095305_message_template_with_event extends \app\classes\Migration
{
    public function up()
    {
        $this->addColumn('message_template', 'event_code', $this->string(50)->defaultValue(null));
        $this->addForeignKey('message_template__event_code', 'message_template', 'event_code', 'important_events_names', 'code', 'SET NULL');
    }

    public function down()
    {
        $this->dropForeignKey('message_template__event_code', 'message_template');
        $this->dropColumn('message_template', 'event_code');
    }
}