<?php

class m150717_122836_usage_voip_package extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            CREATE TABLE `usage_voip_package` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `client` VARCHAR(100) NOT NULL DEFAULT '0',
              `activation_dt` DATETIME NULL DEFAULT NULL,
              `expire_dt` DATETIME NULL DEFAULT '2029-01-01 23:59:59',
              `actual_from` DATE NULL DEFAULT NULL,
              `actual_to` DATE NULL DEFAULT '2029-01-01',
              `tariff_id` INT(11) NOT NULL DEFAULT '0',
              `usage_voip_id` INT(11) NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              INDEX `client` (`client`)
            )
        ");
    }

    public function down()
    {
        echo "m150717_122836_usage_voip_package cannot be reverted.\n";

        return false;
    }
}