<?php

/**
 * Class m180323_160752_access_dictonary_business_process_status
 */
class m180323_160752_access_dictonary_business_process_status extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addUserRights('dictionary-statuses', 'Справочник: статусы бизнес процессов');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropUserRights('dictionary-statuses');
    }
}
