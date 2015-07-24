<?php

class m150723_180421_tax_regime extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `client_contragent`
            	CHANGE COLUMN `tax_regime` `tax_regime` ENUM('0','1') NOT NULL DEFAULT '1' AFTER `fioV`;

            UPDATE `client_contragent` SET `tax_regime` = 1;
        ");
    }

    public function down()
    {
        echo "m150723_180421_tax_regime cannot be reverted.\n";

        return false;
    }
}