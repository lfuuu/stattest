<?php

use app\classes\Migration;
use app\models\BillLineUu;
use app\modules\uu\models\AccountEntry;

/**
 * Class m220731_143647_bill_lines_uu
 */
class m220731_143647_bill_lines_uu extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(BillLineUu::tableName(), [
            'uu_account_entry_id' => $this->integer(),
            'bill_no' => 'varchar(32) CHARACTER SET utf8mb3 COLLATE utf8_bin NOT NULL',
            'sort' => $this->integer()->notNull()->defaultValue(0),
            'item' => $this->string(200)->notNull()->defaultValue(''),
            'amount' => $this->decimal(13, 6)->defaultValue(0),
            'price' => $this->decimal(13, 4)->defaultValue(0),
            'sum' => $this->decimal(11, 2)->defaultValue(0),
            'discount_set' => $this->decimal(11,4)->notNull()->defaultValue(0),
            'discount_auto' => $this->decimal(11,4)->notNull()->defaultValue(0),
            'service' => $this->string(20)->notNull()->defaultValue(''),
            'id_service' => $this->integer()->defaultValue(0),
            'date_from' => $this->date()->notNull(),
            'date_to' => $this->date()->notNull(),
            'type' => $this->string(32)->notNull(),
            'tax_rate' => $this->integer(),
            'sum_without_tax' => $this->decimal(11, 2),
            'sum_tax' => $this->decimal(11, 2),
            'cost_price' => $this->decimal(13, 4)->notNull()->defaultValue(0),
        ]);

        $this->addForeignKey(BillLineUu::tableName() . '-uu_account_entry_id',
            BillLineUu::tableName(), 'uu_account_entry_id',
            AccountEntry::tableName(), 'id',
            'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(BillLineUu::tableName() . '-bill_no',
            BillLineUu::tableName(), 'bill_no',
            \app\models\Bill::tableName(), 'bill_no',
            'CASCADE', 'CASCADE'
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(BillLineUu::tableName());
    }
}
