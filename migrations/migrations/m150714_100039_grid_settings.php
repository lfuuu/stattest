<?php

class m150714_100039_grid_settings extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("DROP VIEW `client_grid_statuses`;");
        $this->executeSqlFile('import.sql');
    }

    public function down()
    {
        echo "m150714_100039_grid_settings cannot be reverted.\n";

        return false;
    }
}