<?php

/**
 * Class m240722_155001_cache_table
 */
class m240722_155001_cache_table extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->executeRaw(<<<SQL
     CREATE TABLE z_cache (
         id char(128) NOT NULL PRIMARY KEY,
         expire int(11),
         data LONGBLOB
    );
SQL);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable('z_cache');
    }
}
