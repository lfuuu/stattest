<?php

class m160324_105744_message_template_events extends \app\classes\Migration
{
    public function up()
    {
        $this->createTable('message_templates_events', [
            'template_id' => $this->integer(11)->defaultValue(null),
            'event_code' => $this->string(50)->defaultValue(null),
        ]);
        $this->addPrimaryKey('message_templates_events', 'message_templates_events', ['template_id', 'event_code']);
    }

    public function down()
    {
        $this->dropTable('message_templates_events');
    }
}