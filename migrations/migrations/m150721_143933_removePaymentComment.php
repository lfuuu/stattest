<?php

class m150721_143933_removePaymentComment extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `clients` DROP COLUMN `payment_comment`;
        ");
    }

    public function down()
    {
        echo "m150721_143933_removePaymentComment cannot be reverted.\n";

        return false;
    }
}