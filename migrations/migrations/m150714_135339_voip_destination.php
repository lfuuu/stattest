<?php

class m150714_135339_voip_destination extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            CREATE TABLE `voip_destination` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(50) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $this->execute("
            CREATE TABLE `voip_prefixlist` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(50) NOT NULL,
              `type_id` smallint(6) NOT NULL,
              `prefixes` text,
              `country_id` int(11) DEFAULT NULL,
              `region_id` int(11) DEFAULT NULL,
              `city_id` int(11) DEFAULT NULL,
              `exclude_operators` tinyint(1) DEFAULT NULL,
              `operators` text,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $this->execute("
            CREATE TABLE `voip_destination_prefixes` (
                `destination_id` INT(11) NOT NULL,
                `prefixlist_id` INT(11) NOT NULL,
                KEY `destination_id` (`destination_id`),
                KEY `prefixlist_id` (`prefixlist_id`),
                CONSTRAINT `fk_destination_prefixes__destination_id` FOREIGN KEY (`destination_id`) REFERENCES `voip_destination` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk_destination_prefixes__pricelist_id` FOREIGN KEY (`prefixlist_id`) REFERENCES `voip_prefixlist` (`id`) ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    public function down()
    {
        echo "m150714_135339_destination cannot be reverted.\n";

        return false;
    }
}