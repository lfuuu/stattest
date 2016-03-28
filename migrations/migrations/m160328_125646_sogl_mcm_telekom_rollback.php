<?php

class m160328_125646_sogl_mcm_telekom_rollback extends \app\classes\Migration
{
    public function up()
    {
        $this->alterColumn(
            'mail_object',
            'object_type',
            "enum('bill','PM','assignment','order','notice','invoice','akt','new_director_info','upd','lading','notice_mcm_telekom','sogl_mcm_telekom')"
        );
    }

    public function down()
    {
        $this->alterColumn(
            'mail_object',
            'object_type',
            "enum('bill','PM','assignment','order','notice','invoice','akt','new_director_info','upd','lading','notice_mcm_telekom')"
        );
    }
}