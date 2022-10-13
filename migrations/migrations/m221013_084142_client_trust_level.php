<?php

use app\classes\Migration;
use app\models\dictionary\TrustLevel;

/**
 * Class m221013_084142_client_trust_level
 */
class m221013_084142_client_trust_level extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(TrustLevel::tableName(), [
            'id' => $this->integer()->notNull(),
            'name' => $this->string(1024)->notNull(),
            'order' => $this->integer()->notNull()->defaultValue(0),
        ]);

        $this->createIndex('idx-' . TrustLevel::tableName() . '-id', TrustLevel::tableName(), ['id'], true);

        $data = [
            ['0', 'Не установлен', 1],
            ['1', 'Низкий', 2],
            ['2', 'Средний', 3],
            ['3', 'Высокий', 4],
            ['4', 'ОТТ / Низкий', 5],
            ['5', 'ОТТ / Средний', 6],
            ['6', 'ОТТ / Высокий', 7],
            ['7', 'Контакт-центры / Низкий', 8],
            ['8', 'Контакт-центры / Средний', 9],
            ['9', 'Контакт-центры / Высокий', 10],
            ['10', 'Робокол MCN', 11],
            ['11', 'Робокол', 12],
        ];

        $this->batchInsert(TrustLevel::tableName(), ['id', 'name', 'order'], $data);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(TrustLevel::tableName());
    }
}
