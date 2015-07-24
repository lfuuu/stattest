<?php

class m150722_120829_mail_object_sogl_mcm extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `mail_object`
            MODIFY COLUMN `object_type`  
                enum('bill','PM','assignment',
                    'order','notice','invoice',
                    'akt','new_director_info','upd','lading','sogl_mcm_telekom') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'bill' AFTER `client_id`;
        ");

    }

    public function down()
    {
        echo "m150722_120829_mail_object_sogl_mcm cannot be reverted.\n";

        return false;
    }
}
