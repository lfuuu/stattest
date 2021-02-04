<?php

/**
 * Class m210204_122143_sorm_redirects
 */
class m210204_122143_sorm_redirects extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable('sorm_redirects', [
            'id' => $this->primaryKey(),
            'account_id' => $this->integer()->notNull(),
            'did' => $this->char(11)->notNull(),
            'type' => "enum ('noanswer', 'unavail', 'uncond', 'busy') not null",
            'redirect_number' => $this->char(20)->notNull(),
            'insert_time' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'delete_time' => $this->dateTime()
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8'
        );

        $this->createIndex('sorm_redirects__idx', 'sorm_redirects', ['account_id', 'did', 'type', 'redirect_number'], true);
        $this->createIndex('sorm_redirects__idx_delete', 'sorm_redirects', ['delete_time'], true);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable('sorm_redirects');
    }
}
