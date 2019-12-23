<?php

use app\models\ClientCounter;

/**
 * Class m191223_122802_balance_bonuce
 */
class m191223_122802_balance_bonuce extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(ClientCounter::tableName(), 'sum_w_neg_rate', $this->decimal(12, 2)->notNull()->defaultValue(0));
        $this->addColumn(ClientCounter::tableName(), 'sum_w_neg_rate_day', $this->decimal(12, 2)->notNull()->defaultValue(0));
        $this->addColumn(ClientCounter::tableName(), 'sum_w_neg_rate_month', $this->decimal(12, 2)->notNull()->defaultValue(0));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(ClientCounter::tableName(), 'sum_w_neg_rate');
        $this->dropColumn(ClientCounter::tableName(), 'sum_w_neg_rate_day');
        $this->dropColumn(ClientCounter::tableName(), 'sum_w_neg_rate_month');

    }
}
