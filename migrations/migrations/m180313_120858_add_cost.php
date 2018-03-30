<?php

use app\models\BillLine;
use app\models\Transaction;

/**
 * Class m180313_120858_add_cost
 */
class m180313_120858_add_cost extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Transaction::tableName(), 'cost_price', $this->decimal(13,4)->notNull()->defaultValue(0));
        $this->addColumn(BillLine::tableName(), 'cost_price', $this->decimal(13,4)->notNull()->defaultValue(0));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(BillLine::tableName(), 'cost_price');
        $this->dropColumn(Transaction::tableName(), 'cost_price');
    }
}
