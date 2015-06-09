<?php

class m150605_112533_i18n extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `country`
	            ADD COLUMN `lang` VARCHAR (2) NULL DEFAULT 'ru' AFTER `in_use`;
        ");

        $this->execute("
            ALTER TABLE `client_contragent`
	            ADD COLUMN `country_code` INT (4) NULL DEFAULT '0' AFTER `super_id`;
        ");
    }

    public function down()
    {
        echo "m150605_112533_i18n cannot be reverted.\n";

        return false;
    }
}