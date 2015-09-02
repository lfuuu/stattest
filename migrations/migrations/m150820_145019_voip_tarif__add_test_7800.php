<?php

class m150820_145019_voip_tarif__add_test_7800 extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE `tarifs_voip`
            MODIFY COLUMN `status`  enum('public','special','archive','7800','test','operator') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'public' AFTER `type_count`;
        ");

    }

    public function down()
    {
        echo "m150820_145019_voip_tarif__add_test_8800 cannot be reverted.\n";

        return false;
    }
}
