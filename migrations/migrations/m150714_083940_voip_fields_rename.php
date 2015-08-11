<?php

class m150714_083940_voip_fields_rename extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `tarifs_voip`
                CHANGE COLUMN `region` `connection_point_id` INT(11) NULL DEFAULT '0' AFTER `country_id`,
                CHANGE COLUMN `currency` `currency_id` CHAR(3) NOT NULL DEFAULT 'USD' COLLATE 'utf8_bin' AFTER `dest`;
        ");
    }

    public function down()
    {
        echo "m150714_083940_voip_fields_rename cannot be reverted.\n";

        return false;
    }
}