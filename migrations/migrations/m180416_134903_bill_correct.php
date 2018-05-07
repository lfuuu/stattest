<?php

use app\models\Bill;
use app\models\BillCorrection;
use app\models\BillLine;
use app\models\BillLineCorrection;
use app\modules\uu\models\AccountEntry;

/**
 * Class m180416_134903_bill_correct
 */
class m180416_134903_bill_correct extends \app\classes\Migration
{
    private $_billNoDefinition = 'varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL';
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Bill::tableName(), 'sum_correction', $this->decimal(11, 2));

        $this->createTable(BillCorrection::tableName(), [
            'id' => $this->primaryKey(),
            'bill_no' => $this->_billNoDefinition,
            'type_id' => $this->integer()->notNull()->defaultValue(BillCorrection::TYPE_INVOICE_1),
            'number' => $this->integer()->notNull()->defaultValue(1),
            'date' => $this->date()->notNull(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->createTable(BillLineCorrection::tableName(), [
            'bill_correction_id' => $this->integer()->notNull(),
            'pk' => $this->primaryKey(),
            'bill_no' => $this->_billNoDefinition, // $this->string(32)->notNull(),
            'sort' => $this->integer()->notNull()->defaultValue(0),
            'item' => $this->string(200)->notNull()->defaultValue(''),
            'amount' => $this->decimal(13, 6)->notNull()->defaultValue(0),
            'price' => $this->decimal(13, 4)->notNull()->defaultValue(0),
            'sum' => $this->decimal(11, 2)->notNull()->defaultValue(0),
            'date_from' => $this->date()->notNull()->defaultValue(BillLine::DATE_DEFAULT),
            'date_to' => $this->date()->notNull()->defaultValue(BillLine::DATE_DEFAULT),
            'type' => "enum('service','zalog','zadatok','good','all4net') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'service'",
            'tax_rate' => $this->integer(11)->notNull()->defaultValue(0),
            'sum_without_tax' => $this->decimal(11, 2)->notNull()->defaultValue(0),
            'sum_tax' => $this->decimal(11, 2)->notNull()->defaultValue(0),
        ], 'ENGINE = InnoDB DEFAULT CHARSET = utf8'
        );

        $this->createIndex('bill_sort', BillLineCorrection::tableName(), ['bill_no', 'sort']);

        $this->addForeignKey('fk-' . BillLineCorrection::tableName() . '-bill_correction_id',
            BillLineCorrection::tableName(), 'bill_correction_id',
            BillCorrection::tableName(), 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Bill::tableName(), 'sum_correction');
        $this->dropTable(BillLineCorrection::tableName());
        $this->dropTable(BillCorrection::tableName());
    }
}
