<?php

class m150805_093459_wizard__is_bonus_added extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE `lk_wizard_state`
            ADD COLUMN `is_bonus_added`  tinyint NOT NULL DEFAULT 0,
            ADD COLUMN `is_on`  tinyint NOT NULL DEFAULT 1
        ");

    }

    public function down()
    {
        echo "m150805_093459_wizard__is_bonus_added cannot be reverted.\n";

        return false;
    }
}
