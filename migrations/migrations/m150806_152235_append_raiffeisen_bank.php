<?php

class m150806_152235_append_raiffeisen_bank extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `newpayments`
                CHANGE COLUMN `bank` `bank` ENUM('citi','mos','ural','sber','raiffeisen') NOT NULL DEFAULT 'mos' AFTER `add_user`;
        ");
    }

    public function down()
    {
        echo "m150806_152235_append_raiffeisen_bank cannot be reverted.\n";

        return false;
    }
}