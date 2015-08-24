<?php

class m150824_113528_client_contragent_tax_regime_v2 extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `client_contragent`
                CHANGE COLUMN `tax_regime` `tax_regime` ENUM('undefined','russia_full_price_vat','without_price_vat','OCH-VAT18','YCH-VAT0') NOT NULL DEFAULT 'russia_full_price_vat' AFTER `signer_passport`;
        ");

        $this->execute("
            UPDATE `client_contragent` SET `tax_regime` = 'OCH-VAT18' WHERE `tax_regime` = 'russia_full_price_vat';
            UPDATE `client_contragent` SET `tax_regime` = 'YCH-VAT0' WHERE `tax_regime` = 'without_price_vat';
        ");

        $this->execute("
            ALTER TABLE `client_contragent`
                CHANGE COLUMN `tax_regime` `tax_regime` ENUM('undefined','OCH-VAT18','YCH-VAT0') NOT NULL DEFAULT 'OCH-VAT18' AFTER `signer_passport`;
        ");
    }

    public function down()
    {
        echo "m150824_113528_client_contragent_tax_regime_v2 cannot be reverted.\n";

        return false;
    }
}