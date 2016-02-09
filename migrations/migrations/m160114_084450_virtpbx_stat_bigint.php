<?php

class m160114_084450_virtpbx_stat_bigint extends \app\classes\Migration
{
    public function up()
    {
        $this->execute('
            ALTER TABLE `virtpbx_stat`
                CHANGE COLUMN `use_space` `use_space` BIGINT NULL DEFAULT "0",
                ADD COLUMN `ext_did_count` INT(11) NULL DEFAULT NULL;
        ');
    }

    public function down()
    {
        echo "m160114_084450_virtpbx_stat_bigint cannot be reverted.\n";

        return false;
    }
}