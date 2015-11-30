<?php

class m151130_143254_fix_lang_code extends \app\classes\Migration
{
    public function up()
    {
        $this->execute('
            ALTER TABLE `country`
                CHANGE COLUMN `lang` `lang` VARCHAR(5) NULL DEFAULT "ru-RU" AFTER `in_use`;
        ');

        $this->execute('
            ALTER TABLE `organization`
                CHANGE COLUMN `lang_code` `lang_code` VARCHAR(5) NOT NULL DEFAULT "ru-RU" AFTER `country_id`;
        ');

        $this->execute('
            UPDATE `organization` SET `lang_code` = "ru-RU" WHERE `lang_code` = "ru";
        ');

        $this->execute('
            UPDATE `organization` SET `lang_code` = "hu-HU" WHERE `lang_code` = "hu";
        ');
    }

    public function down()
    {
        echo "m151130_143254_fix_lang_code cannot be reverted.\n";

        return false;
    }
}