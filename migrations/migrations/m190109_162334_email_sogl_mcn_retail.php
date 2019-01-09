<?php

/**
 * Class m190109_162334_email_sogl_mcn_retail
 */
class m190109_162334_email_sogl_mcn_retail extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn('mail_object', 'object_type', "enum('bill','PM','assignment','order','notice','invoice','akt','new_director_info','upd','lading','notice_mcm_telekom','sogl_mcm_telekom','sogl_mcn_telekom', 'sogl_mcn_service') DEFAULT NULL");
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn('mail_object', 'object_type', "enum('bill','PM','assignment','order','notice','invoice','akt','new_director_info','upd','lading','notice_mcm_telekom','sogl_mcm_telekom','sogl_mcn_telekom') DEFAULT NULL");
    }
}
