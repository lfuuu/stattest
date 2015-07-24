<?php

class m150723_180421_tax_regime extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `client_contragent` ADD COLUMN `t` ENUM('0','1') NOT NULL DEFAULT '1' AFTER `tax_regime`;
            UPDATE `client_contragent` SET `t` = '0';
            UPDATE `client_contragent` SET `t` = '1' WHERE `tax_regime` != 'simplified';
            ALTER TABLE `client_contragent` DROP COLUMN `tax_regime`;
            ALTER TABLE `client_contragent` CHANGE COLUMN `t` `tax_regime` ENUM('0','1') NOT NULL DEFAULT '1' AFTER `fioV`;
        ");
    }

    public function down()
    {
        echo "m150723_180421_tax_regime cannot be reverted.\n";

        return false;
    }
}