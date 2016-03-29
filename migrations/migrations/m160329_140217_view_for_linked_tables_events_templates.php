<?php

class m160329_140217_view_for_linked_tables_events_templates extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
	    CREATE VIEW
		view_message_templates_events_ro
	    AS SELECT
		template_id,
		event_code
	    FROM message_templates_events;
        ");
    }

    public function down()
    {
	$this->execute("DROP VIEW view_message_templates_events_ro;");
    }
}