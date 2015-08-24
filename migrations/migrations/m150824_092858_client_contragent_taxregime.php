<?php

class m150824_092858_client_contragent_taxregime extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `client_contragent`
                CHANGE COLUMN `tax_regime` `tax_regime` ENUM('0','1','2') NOT NULL DEFAULT '1' AFTER `signer_passport`;
        ");
    }

    public function down()
    {
        echo "m150824_092858_client_contragent_taxregime cannot be reverted.\n";

        return false;
    }
}