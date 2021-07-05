<?php

use app\models\Invoice;
use app\models\InvoiceLine;

/**
 * Class m190210_104810_invoice_lines
 */
class m190210_104810_invoice_lines extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(InvoiceLine::tableName(), [
            'pk' => $this->primaryKey(),
            'invoice_id' => $this->integer(),
            'sort' => $this->integer()->notNull()->defaultValue(0),
            'item' => $this->string(200)->notNull()->defaultValue(''),
            'amount' => $this->decimal(13, 6)->notNull()->defaultValue(0),
            'price' => $this->decimal(13, 4)->notNull()->defaultValue(0),
            'sum' => $this->decimal(12, 2)->notNull()->defaultValue(0),
            'date_from' => $this->date()->notNull()->defaultValue(null),
            'date_to' => $this->date()->notNull()->defaultValue(null),
            'type' => "enum('service','zalog','zadatok','good','all4net') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'service'",
            'tax_rate' => $this->integer()->notNull()->defaultValue(0),
            'sum_without_tax' => $this->decimal(11, 2)->notNull()->defaultValue(0),
            'sum_tax' => $this->decimal(11, 2)->notNull()->defaultValue(0)
        ]);

        $this->addForeignKey('fk-' . Invoice::tableName() . '-' . InvoiceLine::tableName(),
            InvoiceLine::tableName(), 'invoice_id',
            Invoice::tableName(), 'id', 'CASCADE', 'CASCADE'
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-' . Invoice::tableName() . '-' . InvoiceLine::tableName(), InvoiceLine::tableName());
        $this->dropTable(InvoiceLine::tableName());
    }
}
