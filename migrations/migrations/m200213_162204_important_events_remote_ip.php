<?php

/**
 * Class m200213_162204_important_events_remote_ip
 */
class m200213_162204_important_events_remote_ip extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {

        $sql = <<<SQL
ALTER TABLE `important_events`  ADD remote_ip VARCHAR(16) DEFAULT NULL, ADD login VARCHAR(255) DEFAULT NULL 
SQL;
        $this->execute($sql);

    }



    /**
     * Down
     */
    public function safeDown()
    {
        $sql = <<<SQL
ALTER TABLE `important_events`  DROP remote_ip , DROP login
SQL;
    }
}
