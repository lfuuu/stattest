<?php

class m160120_150349_lk_notice_remove_koi8r extends \app\classes\Migration
{
    public function up()
    {
        $this->execute('
            ALTER TABLE `lk_notice`
                COLLATE="utf8_general_ci",
                CONVERT TO CHARSET utf8;
        ');
    }

    public function down()
    {
        echo "m160120_150349_lk_notice_remove_koi8r cannot be reverted.\n";

        return false;
    }
}