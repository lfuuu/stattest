<?php

class m150512_191804_support_ticket__flag_new_comment extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE `support_ticket`
        ADD COLUMN `is_with_new_comment`  tinyint NOT NULL DEFAULT 0 AFTER `status`;");

    }

    public function down()
    {
        echo "m150512_191804_support_ticket__flag_new_comment cannot be reverted.\n";

        return false;
    }
}
