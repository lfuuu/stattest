<?php

class m150812_165321_opf extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            CREATE TABLE `code_opf` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `code` VARCHAR(10) NOT NULL DEFAULT '',
                `name` VARCHAR(255) NOT NULL DEFAULT '',
                PRIMARY KEY (`id`)
            )
            ENGINE=InnoDB;

            ALTER TABLE `client_contragent`
        	  CHANGE COLUMN `opf` `opf_id` INT(11) NOT NULL DEFAULT '0' AFTER `tax_regime`;

              INSERT INTO `code_opf` (`code`, `name`) VALUES ('1100', 'Предприятия');
              INSERT INTO `code_opf` (`code`, `name`) VALUES ('1110', 'Государственные предприятия');
              INSERT INTO `code_opf` (`code`, `name`) VALUES ('1230', 'Объединения предприятий акционерного общества');
        ");
    }

    public function down()
    {
        echo "m150812_165321_opf cannot be reverted.\n";

        return false;
    }
}