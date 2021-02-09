<?php

/**
 * Class m210208_164235_sorm_redirect_ranges
 */
class m210208_164235_sorm_redirect_ranges extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable('sorm_redirect_ranges', [
            'usage_id' => $this->integer()->notNull(),
            'did' => $this->string(32)->notNull(),
            'type' => $this->string(32)->notNull(),
            'numbers' => $this->string(1024)->notNull(),
            'open_time' => $this->dateTime()->notNull(),
            'close_time' => $this->dateTime()
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->createIndex('sorm_redirect_ranges_pk', 'sorm_redirect_ranges', ['usage_id', 'did', 'type', 'open_time'], true);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable('sorm_redirect_ranges');
    }
}
