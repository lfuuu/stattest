<?php

class m151110_125703_lk_balance_view_mode extends \app\classes\Migration
{
    public function up()
    {
        $this->execute('
            ALTER TABLE `clients`
                ADD COLUMN `lk_balance_view_mode` ENUM("old", "new") NOT NULL DEFAULT "old" AFTER `timezone_offset`;
        ');
    }

    public function down()
    {
        echo "m151110_125703_lk_balance_view_mode cannot be reverted.\n";

        return false;
    }
}