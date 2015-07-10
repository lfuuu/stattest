<?php

class m150707_152123_inc_vat_to_tariff extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("DROP TABLE `tarifs_saas`;");
        $this->execute("DROP TABLE `tarifs_hosting`;");

        $this->execute("
            ALTER TABLE `clients`
                ADD COLUMN `price_include_vat` TINYINT(1) NULL DEFAULT '1' AFTER `contract_type_id`;
        ");

        $this->execute("
            UPDATE `clients` c LEFT JOIN `organization` o ON
                c.`organization_id` = o.`organization_id` AND
                o.`actual_from` <= CAST(NOW() AS DATE) AND
                o.`actual_to` > CAST(NOW() AS DATE)
            SET c.`price_include_vat` = 0
            WHERE c.`contract_type_id` = 3 OR c.`country_id` != 643
        ");

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
                ADD COLUMN `price_include_vat` TINYINT(1) NULL DEFAULT '1' AFTER `is_virtual`;
        ");
    }

    public function down()
    {
        echo "m150707_152123_inc_vat_to_tariff cannot be reverted.\n";

        return false;
    }
}