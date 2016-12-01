<?php

class m161201_114445_sogl_mcm2 extends \app\classes\Migration
{
    public function up()
    {
        $this->alterColumn("mail_object", "object_type", "enum('bill','PM','assignment','order','notice','invoice','akt','new_director_info','upd','lading','notice_mcm_telekom','sogl_mcm_telekom','sogl_mcn_telekom') DEFAULT NULL");
    }

    public function down()
    {
        $this->alterColumn("mail_object", "object_type", "enum('bill','PM','assignment','order','notice','invoice','akt','new_director_info','upd','lading','notice_mcm_telekom','sogl_mcm_telekom') DEFAULT NULL");
    }
}