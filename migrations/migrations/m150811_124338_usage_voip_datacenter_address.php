<?php

class m150811_124338_usage_voip_datacenter_address extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `usage_voip`
                ADD COLUMN `address_from_datacenter_id` INT(11) NULL DEFAULT NULL AFTER `address`,
                ADD CONSTRAINT `fk_usage_voip__address_from_datacenter_id` FOREIGN KEY (`address_from_datacenter_id`) REFERENCES `datacenter` (`id`) ON UPDATE CASCADE;
        ");
    }

    public function down()
    {
        echo "m150811_124338_usage_voip_datacenter_address cannot be reverted.\n";

        return false;
    }
}