<?php

class m160120_144318_lk_notice_localization extends \app\classes\Migration
{
    public function up()
    {
        $this->execute('
            ALTER TABLE `lk_notice`
                ADD COLUMN `lang` VARCHAR(5) NULL DEFAULT "ru-RU";
        ');
    }

    public function down()
    {
        echo "m160120_144318_lk_notice_localization cannot be reverted.\n";

        return false;
    }
}