<?php

class m150903_115053_remove_sogl_mcm_telekom extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `mail_object`
            MODIFY COLUMN `object_type`
                ENUM('bill','PM','assignment',
                    'order','notice','invoice',
                    'akt','new_director_info','upd','lading','notice_mcm_telekom') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'bill' AFTER `client_id`;
        ");
    }

    public function down()
    {
        echo "m150903_115053_remove_sogl_mcm_telekom cannot be reverted.\n";

        return false;
    }
}