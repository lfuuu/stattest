<?php

class m150605_112533_i18n extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `country`
	            ADD COLUMN `lang` VARCHAR (5) NULL DEFAULT 'ru' AFTER `in_use`;
        ");
        $this->execute("
            UPDATE `country` SET `lang` = 'hu' WHERE `code` = 348;
        ");

        $this->execute("
            ALTER TABLE `client_contragent`
	            ADD COLUMN `country_id` INT (4) NULL DEFAULT '643' AFTER `super_id`;
        ");
    }

    public function down()
    {
        echo "m150605_112533_i18n cannot be reverted.\n";

        return false;
    }
}