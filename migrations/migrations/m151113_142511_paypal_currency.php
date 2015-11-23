<?php

class m151113_142511_paypal_currency extends \app\classes\Migration
{
    public function up()
    {
        $this->execute(
            "ALTER TABLE `paypal_payment` ADD COLUMN `currency`  char(3) NOT NULL DEFAULT 'HUF' AFTER `client_id`"
        );

        $this->execute(
            "ALTER TABLE `paypal_payment` ADD COLUMN `created_at` datetime NOT NULL AFTER `token`"
        );
    }

    public function down()
    {
        echo "m151113_142511_paypal_currency cannot be reverted.\n";

        return false;
    }
}
