<?php

class m160127_101016_remove_is_moved_field extends \app\classes\Migration
{
    public function up()
    {
        $this->dropColumn('usage_virtpbx', 'is_moved');
        $this->dropColumn('usage_voip', 'is_moved');
        $this->dropColumn('usage_voip', 'is_moved_with_pbx');
    }

    public function down()
    {
        $this->addColumn('usage_virtpbx', 'is_moved', 'INT(1) NOT NULL DEFAULT "0"');
        $this->addColumn('usage_voip', 'is_moved', 'INT(1) NOT NULL DEFAULT "0"');
        $this->addColumn('usage_voip', 'is_moved_with_pbx', 'INT(1) NOT NULL DEFAULT "0"');
    }
}