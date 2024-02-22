<?php

use app\classes\Migration;
use app\models\ClientContragentImportLkStatus;
use app\models\ClientContragent;

/**
 * Class m240222_131135_contragent_import_status
 */
class m240222_131135_contragent_import_status extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(ClientContragentImportLkStatus::tableName(), [
            'contragent_id' => $this->primaryKey(),
            'updated_at' => $this->timestamp()->notNull(),
            'status_code' => $this->string(32)->notNull(),
            'status_text' => $this->text()
        ]);

        $this->addForeignKey(ClientContragent::tableName() . '-import-lk-status',
            ClientContragentImportLkStatus::tableName(), 'contragent_id',
            ClientContragent::tableName(), 'id',
            'CASCADE', 'RESTRICT'
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(ClientContragentImportLkStatus::tableName());
    }
}
