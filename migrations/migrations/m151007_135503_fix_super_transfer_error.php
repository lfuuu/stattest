<?php

class m151007_135503_fix_super_transfer_error extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            UPDATE `client_contragent` SET
                `super_id` = 77777
            WHERE
                `id` = 79572;
        ");
    }

    public function down()
    {
        echo "m151007_135503_fix_super_transfer_error cannot be reverted.\n";

        return false;
    }
}
