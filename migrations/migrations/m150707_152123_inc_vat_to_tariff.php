<?php

class m150707_152123_inc_vat_to_tariff extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("DROP TABLE `tarifs_saas`;");
        $this->execute("DROP TABLE `tarifs_hosting`;");

        $this->execute("
            ALTER TABLE `tarifs_internet`
                ADD COLUMN `price_include_vat` TINYINT(1) NULL DEFAULT '1' AFTER `adsl_speed`;
        ");
        $this->execute("
            UPDATE `tarifs_internet` SET
                `pay_once` = ROUND(`pay_once` + (`pay_once` * 0.18), 2),
                `pay_month` = ROUND(`pay_month` + (`pay_month` * 0.18), 2),
                `pay_mb` = ROUND(`pay_mb` + (`pay_mb` * 0.18), 2);
        ");

        $this->execute("
            ALTER TABLE `tarifs_extra`
                ADD COLUMN `price_include_vat` TINYINT(1) NULL DEFAULT '1' AFTER `okvd_code`;
        ");
        $this->execute("
            UPDATE `tarifs_extra` SET
                `price` = ROUND(`price` + (`price` * 0.18), 2);
        ");

        $this->execute("
            ALTER TABLE `tarifs_virtpbx`
                ADD COLUMN `price_include_vat` TINYINT(1) NULL DEFAULT '1' AFTER `edit_time`;
        ");
        $this->execute("
            UPDATE `tarifs_virtpbx` SET
                `price` = ROUND(`price` + (`price` * 0.18), 2);
        ");

        $this->execute("
            ALTER TABLE `tarifs_sms`
                ADD COLUMN `price_include_vat` TINYINT(1) NULL DEFAULT '1' AFTER `edit_time`;
        ");
        $this->execute("
            UPDATE `tarifs_sms` SET
                `per_month_price` = ROUND(`per_month_price` + (`per_month_price` * 0.18), 2);
        ");

        $this->execute("
            ALTER TABLE `tarifs_voip`
                ADD COLUMN `price_include_vat` TINYINT(1) NULL DEFAULT '1' AFTER `is_virtual`,
                ADD COLUMN `type` ENUM('client','operator') NOT NULL DEFAULT 'client' AFTER `price_include_vat`;
        ");
        $this->execute("
            UPDATE `tarifs_voip` SET `type`='operator' WHERE `status`='operator';

            UPDATE `tarifs_voip` SET
                `month_line` = ROUND(`month_line` + (`month_line` * 0.18), 2),
                `month_number` = ROUND(`month_number` + (`month_number` * 0.18), 2),
                `month_min_payment` = ROUND(`month_min_payment` + (`month_min_payment` * 0.18), 2),
                `once_line` = ROUND(`once_line` + (`once_line` * 0.18), 2),
                `once_number` = ROUND(`once_number` + (`once_number` * 0.18), 2)
            WHERE `type` != 'operator';
        ");
    }

    public function down()
    {
        echo "m150707_152123_inc_vat_to_tariff cannot be reverted.\n";

        return false;
    }
}