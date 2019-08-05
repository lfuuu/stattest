<?php

use app\models\BalanceByMonth;

/**
 * Class m190731_131029_balance_month
 */
class m190731_131029_balance_month extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(BalanceByMonth::tableName(), [
            'account_id' => $this->integer()->notNull(),
            'year' => $this->integer()->notNull(),
            'month' => $this->integer()->notNull(),
            'balance' => $this->decimal(12, 2),
        ]);

        $this->addPrimaryKey('pk-' . BalanceByMonth::tableName(), BalanceByMonth::tableName(), ['account_id', 'year', 'month']);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(BalanceByMonth::tableName());
    }
}
