<?php

class m150227_134444_tickets extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `support_ticket`
            DROP COLUMN `service_type`,
            ADD COLUMN `department`  enum('sales','accounting','technical') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'technical' AFTER `status`;
        ");
    }

    public function down()
    {
        echo "m150227_134444_tickets cannot be reverted.\n";

        return false;
    }
}