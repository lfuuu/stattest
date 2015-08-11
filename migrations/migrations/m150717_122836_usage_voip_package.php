<?php

class m150717_122836_usage_voip_package extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            CREATE TABLE `usage_voip_package` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `client` VARCHAR(100) NOT NULL DEFAULT '0',
                `activation_dt` DATETIME NOT NULL,
                `expire_dt` DATETIME NOT NULL DEFAULT '4000-01-01 23:59:59',
                `actual_from` DATE NOT NULL,
                `actual_to` DATE NOT NULL DEFAULT '4000-01-01',
                `tariff_id` INT(11) NOT NULL DEFAULT '0',
                `usage_voip_id` INT(11) NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                INDEX `client` (`client`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    public function down()
    {
        echo "m150717_122836_usage_voip_package cannot be reverted.\n";

        return false;
    }
}