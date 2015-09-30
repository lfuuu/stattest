<?php

class m150930_042129_drop_allowed_direction extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE `actual_number` DROP COLUMN `direction` ");

        $this->execute("ALTER TABLE `usage_voip` DROP COLUMN `allowed_direction` ");

    }

    public function down()
    {
        echo "m150930_042129_drop_allowed_direction cannot be reverted.\n";

        return false;
    }
}
