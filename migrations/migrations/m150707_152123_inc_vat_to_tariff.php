<?php

class m150707_152123_inc_vat_to_tariff extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("DROP TABLE `tarifs_saas`;");
        //$this->execute("DROP TABLE `tarifs_hosting`;");

        $this->execute("
          ALTER TABLE `tarifs_internet`
              ADD COLUMN `price_include_vat` TINYINT(1) NULL DEFAULT '1' AFTER `adsl_speed`;
        ");

        $this->execute("
          ALTER TABLE `tarifs_extra`
              ADD COLUMN `price_include_vat` TINYINT(1) NULL DEFAULT '1' AFTER `okvd_code`;
        ");

        $this->execute("
          ALTER TABLE `tarifs_virtpbx`
              ADD COLUMN `price_include_vat` TINYINT(1) NULL DEFAULT '1' AFTER `edit_time`;
        ");

        $this->execute("
          ALTER TABLE `tarifs_sms`
              ADD COLUMN `price_include_vat` TINYINT(1) NULL DEFAULT '1' AFTER `edit_time`;
        ");

        $this->execute("
          ALTER TABLE `tarifs_voip`
              ADD COLUMN `price_include_vat` TINYINT(1) NULL DEFAULT '1' AFTER `is_virtual`
              ADD COLUMN `type` ENUM('client','operator') NOT NULL DEFAULT 'client' AFTER `price_include_vat`;
        ");

        $this->execute("
          UPDATE `tarifs_voip` SET `type`='operator' WHERE `status`='operator';
        ");
    }

    public function down()
    {
        echo "m150707_152123_inc_vat_to_tariff cannot be reverted.\n";

        return false;
    }
}