<?php

/**
 * Class m230818_150237_mail_object_type
 */
class m230818_150237_mail_object_type extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn('mail_object', 'object_type', $this->string(64));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn('mail_object', 'object_type', "enum(
               'bill',
            'PM',
            'assignment',
            'order',
            'notice',
            'invoice',
            'akt',
            'new_director_info',
            'upd','lading',
            'notice_mcm_telekom',
            'sogl_mcm_telekom',
            'sogl_mcn_telekom',
            'sogl_mcn_service',
            'sogl_mcn_telekom_to_service'
    
        ) DEFAULT NULL");
    }
}
