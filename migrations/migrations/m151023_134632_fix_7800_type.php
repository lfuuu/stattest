<?php

class m151023_134632_fix_7800_type extends \app\classes\Migration
{
    public function up()
    {
        $this->execute('
            UPDATE `usage_voip` SET `type_id` = "7800" WHERE `E164` REGEXP "^7800" AND `type_id` != "7800";
        ');
    }

    public function down()
    {
        echo "m151023_134632_fix_7800_type cannot be reverted.\n";

        return false;
    }
}