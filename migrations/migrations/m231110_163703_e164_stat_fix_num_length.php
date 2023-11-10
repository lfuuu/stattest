<?php

use app\classes\Migration;
use app\models\NumberLog;

/**
 * Class m231110_163703_e164_stat_fix_num_length
 */
class m231110_163703_e164_stat_fix_num_length extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->delete(NumberLog::tableName(), "e164 regexp '[^0-9]'");
        $this->alterColumn(NumberLog::tableName(), 'e164', $this->bigInteger()->notNull());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(NumberLog::tableName(), 'e164', 'varchar(11) collate utf8_bin not null');
    }
}
