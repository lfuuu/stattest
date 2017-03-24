<?php
use app\models\ClientFlag;

/**
 * Class m170323_183950_account_flag_table
 */
class m170323_183950_account_flag_table extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(ClientFlag::tableName(), [
            'account_id' => $this->primaryKey(),
            'is_notified_7day' => $this->integer()->notNull()->defaultValue(0),
            'is_notified_3day' => $this->integer()->notNull()->defaultValue(0),
            'is_notified_1day' => $this->integer()->notNull()->defaultValue(0),
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(ClientFlag::tableName());
    }
}
