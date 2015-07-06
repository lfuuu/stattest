<?php

class m150706_092226_wizard_type extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE `lk_wizard_state`
            ADD COLUMN `type`  enum('t2t','mcn') NOT NULL DEFAULT 'mcn' AFTER `trouble_id`");
    }

    public function down()
    {
        echo "m150706_092226_wizard_type cannot be reverted.\n";

        return false;
    }
}
