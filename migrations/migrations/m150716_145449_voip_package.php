<?php

class m150716_145449_voip_package extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            CREATE TABLE `tarifs_voip_package` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `country_id` INT(11) NOT NULL DEFAULT '643',
                `connection_point_id` INT(11) NULL DEFAULT '0',
                `currency_id` CHAR(3) NOT NULL DEFAULT 'USD',
                `destination_id` INT(11) NULL DEFAULT '0',
                `pricelist_id` SMALLINT(6) NULL DEFAULT '0',
                `name` VARCHAR(255) NOT NULL,
                `price_include_vat` TINYINT(1) NULL DEFAULT '1',
                `periodical_fee` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
                `min_payment` INT(11) NOT NULL DEFAULT '0',
                `minutes_count` SMALLINT(6) NOT NULL DEFAULT '0',
                `edit_user` INT(11) NULL DEFAULT NULL,
                `edit_time` DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    public function down()
    {
        echo "m150716_145449_voip_package cannot be reverted.\n";

        return false;
    }
}