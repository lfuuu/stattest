<?php

class m150824_092858_client_contragent_taxregime extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `client_contragent`
                CHANGE COLUMN `tax_regime` `tax_regime` ENUM('0','1','2','undefined','russia_full_price_vat','without_price_vat') NOT NULL DEFAULT '1' AFTER `signer_passport`;
        ");

        $this->execute("
            UPDATE `client_contragent` SET `tax_regime` = '2' WHERE `id` IN (
                SELECT `id` FROM (
                    SELECT cg.`id` FROM `client_contract` cr
                    INNER JOIN `client_contragent` cg ON cr.`contragent_id` = cg.`id`
                    WHERE cr.`organization_id` = 11
                ) z
            );
        ");

        $this->execute("
            UPDATE `client_contragent` SET `tax_regime` = 'undefined' WHERE `tax_regime` = '0';
            UPDATE `client_contragent` SET `tax_regime` = 'russia_full_price_vat' WHERE `tax_regime` = '1';
            UPDATE `client_contragent` SET `tax_regime` = 'without_price_vat' WHERE `tax_regime` = '2';
        ");

        $this->execute("
            ALTER TABLE `client_contragent`
                CHANGE COLUMN `tax_regime` `tax_regime` ENUM('undefined','russia_full_price_vat','without_price_vat') NOT NULL DEFAULT 'russia_full_price_vat' AFTER `signer_passport`;
        ");
    }

    public function down()
    {
        echo "m150824_092858_client_contragent_taxregime cannot be reverted.\n";

        return false;
    }
}