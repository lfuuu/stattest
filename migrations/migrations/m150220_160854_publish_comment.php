<?php

class m150220_160854_publish_comment extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE `client_statuses` ADD COLUMN `is_publish`  tinyint(4) NOT NULL DEFAULT 0 AFTER `ts`");
    }

    public function down()
    {
        echo "m150220_160854_publish_comment cannot be reverted.\n";

        return false;
    }
}
