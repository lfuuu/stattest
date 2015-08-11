<?php

class m150811_175537_hold_log extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `e164_stat`
            MODIFY COLUMN `action`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `e164`;
        ");
    }

    public function down()
    {
        echo "m150811_175537_hold_log cannot be reverted.\n";

        return false;
    }
}