<?php

class m150709_145753_remove_is_use_tax extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE `newbills` DROP COLUMN `is_use_tax`;");
        $this->execute("ALTER TABLE `newbill_lines` DROP COLUMN `is_price_includes_tax`;");
    }

    public function down()
    {
        echo "m150709_145753_remove_is_use_tax cannot be reverted.\n";

        return false;
    }
}