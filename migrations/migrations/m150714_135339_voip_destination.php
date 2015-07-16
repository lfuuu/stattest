<?php

class m150714_135339_voip_destination extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            CREATE TABLE `voip_destination` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(50) NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $this->execute("
            CREATE TABLE `voip_destination_prefixes` (
                `destination_id` INT(11) NOT NULL DEFAULT '0',
                `prefixlist_id` INT(11) NOT NULL DEFAULT '0'
                INDEX `destination_id` (`destination_id`),
                INDEX `prefix_id` (`prefix_id`),
                CONSTRAINT `fk_destination_prefixes__pricelist_id` FOREIGN KEY (`prefix_id`) REFERENCES `voip_prefixlist` (`id`) ON UPDATE CASCADE,
                CONSTRAINT `fk_destination_prefixes__destination_id` FOREIGN KEY (`destination_id`) REFERENCES `voip_destination` (`id`) ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $this->execute("
            CREATE TABLE `voip_prefixlist` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(50) NOT NULL DEFAULT '0',
                `type_id` SMALLINT(6) NOT NULL DEFAULT '0',
                `prefixes` text NOT NULL DEFAULT '0',
                `country_id` INT(11) NOT NULL DEFAULT '0',
                `region_id` INT(11) NOT NULL DEFAULT '0',
                `city_id` INT(11) NOT NULL DEFAULT '0',
                `exclude_operators` INT(11) NOT NULL DEFAULT '0',
                `operators` text NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    public function down()
    {
        echo "m150714_135339_destination cannot be reverted.\n";

        return false;
    }
}