<?php

use app\models\Bill;
use app\models\rewards\RewardBill;
use app\models\rewards\RewardBillLine;

/**
 * Class m210923_170343_reward_bills
 */
class m210923_170343_reward_bills extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(RewardBillLine::tableName(), [
            'id' => $this->primaryKey(),
            'bill_id' => $this->integer()->unsigned(),
            'bill_line_pk' => $this->integer(),
            'sum' => $this->decimal(12,4),
            'log' => $this->text(),
        ]);

        $this->createTable(RewardBill::tableName(), [
            'id' => $this->primaryKey(),
            'bill_id' => $this->integer()->unsigned(),
            'partner_id' => $this->integer(),
            'client_id' => $this->integer(),
            'payment_date' => $this->date(),
            'sum' => $this->decimal(12,4),
        ]);

        $this->addForeignKey(
            'fk-' . RewardBill::tableName() . '-bill_id',
            RewardBill::tableName(),
            'bill_id',
            Bill::tableName(),
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-' . RewardBillLine::tableName() . '-bill_id',
            RewardBillLine::tableName(),
            'bill_id',
            RewardBill::tableName(),
            'bill_id',
            'CASCADE'
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(RewardBillLine::tableName());
        $this->dropTable(RewardBill::tableName());
    }
}
