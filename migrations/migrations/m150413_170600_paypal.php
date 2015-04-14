<?php

class m150413_170600_paypal extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE `newpayments` MODIFY COLUMN `ecash_operator`  enum('uniteller','cyberplat','paypal','yandex') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `type`,
            MODIFY payment_no VARCHAR(32) not null default '0'
            ");

        $this->execute("CREATE TABLE `paypal_payment` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `token` varchar(64) NOT NULL,
            `client_id` int(11) NOT NULL,
            `sum` decimal(12,2) NOT NULL DEFAULT '0.00',
            `payer_id` varchar(64) NOT NULL,
            `payment_id` varchar(64) NOT NULL,
            `data1` text NOT NULL,
            `data2` text NOT NULL,
            `data3` text NOT NULL,
            PRIMARY KEY (`id`),
          KEY `token` (`token`)
          )

          ");

    }

    public function down()
    {
        echo "m150413_170600_paypal cannot be reverted.\n";

        return false;
    }
}
