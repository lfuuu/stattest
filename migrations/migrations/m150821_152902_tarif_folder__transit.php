<?php

class m150821_152902_tarif_folder__transit extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE `tarifs_voip`
            MODIFY COLUMN `status`  enum('public','special','archive','7800','test','operator','transit') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'public' AFTER `type_count`;
        ");

    }

    public function down()
    {
        echo "m150821_152902_tarif_folder__transit cannot be reverted.\n";

        return false;
    }
}
