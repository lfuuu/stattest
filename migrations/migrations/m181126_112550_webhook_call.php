<?php

use app\modules\webhook\models\Call;

/**
 * Class m181126_112550_webhook_call
 */
class m181126_112550_webhook_call extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(Call::tableName(), [
            'id' => $this->primaryKey(),
            'abon' => $this->integer()->notNull()->defaultValue(0),
            'calling_number' => $this->bigInteger()->notNull()->defaultValue(0),
            'call_start' => $this->dateTime()->notNull()
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->createIndex('idx-call_start', Call::tableName(), ['call_start']);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(Call::tableName());
    }
}
