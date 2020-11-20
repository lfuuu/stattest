<?php

use app\classes\Migration;

/**
 * Class m201120_141516_sessions_to_db
 */
class m201120_141516_sessions_to_db extends Migration
{
    protected $tableName = 'session';

    /**
     * Up
     */
    public function safeUp()
    {
        $query = <<<SQL
CREATE TABLE {$this->tableName} (
    id CHAR(40) NOT NULL PRIMARY KEY,
    expire INTEGER,
    data BLOB
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL;
        $this->execute($query);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
