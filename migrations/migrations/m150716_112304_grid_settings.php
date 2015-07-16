<?php

class m150716_112304_grid_settings extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
        DROP VIEW IF EXISTS `client_grid_statuses`;
        DROP TABLE IF EXISTS `grid_settings`;
        ");
    }

    public function down()
    {
        echo "m150716_112304_grid_settings cannot be reverted.\n";

        return false;
    }
}