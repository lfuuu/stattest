<?php

class m160311_102438_add_views_for_dbro extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            CREATE VIEW view_important_events_names_ro
            AS SELECT
                `important_events_names`.`id` AS `id`,
                `important_events_names`.`code` AS `code`,
                `important_events_names`.`value` AS `value`,
                `important_events_names`.`group_id` AS `group_id`
            FROM `important_events_names`;
        ");
        $this->execute("
            CREATE VIEW view_important_events_ro
            AS SELECT
                `important_events`.`id` AS `id`,
                `important_events`.`date` AS `date`,
                `important_events`.`event` AS `event`,
                `important_events`.`source_id` AS `source_id`,
                `important_events`.`client_id` AS `client_account_id`
            FROM `important_events`;
        ");
        $this->execute("
            CREATE VIEW view_client_account_contacts_ro
            AS SELECT
                id AS id,
                client_id as client_account_id,
                data as data,
                type as type,
                is_active as is_active
            FROM `client_contacts`;
        ");
    }

    public function down()
    {
        $this->execute("DROP VIEW view_important_events_ro;");
        $this->execute("DROP VIEW view_important_events_names_ro;");
        $this->execute("DROP VIEW view_client_account_contacts_ro;");
    }
}
