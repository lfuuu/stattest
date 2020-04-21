<?php

use app\models\ClientCounter;

/**
 * Class m200323_112832_client_counter_ext
 */
class m200323_112832_client_counter_ext extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(ClientCounter::tableName(), 'voice_sum_day', $this->decimal(12, 2)->notNull()->defaultValue(0));
        $this->addColumn(ClientCounter::tableName(), 'voice_sum_month', $this->decimal(12, 2)->notNull()->defaultValue(0));
        $this->addColumn(ClientCounter::tableName(), 'data_sum_day', $this->decimal(12, 2)->notNull()->defaultValue(0));
        $this->addColumn(ClientCounter::tableName(), 'data_sum_month', $this->decimal(12, 2)->notNull()->defaultValue(0));
        $this->addColumn(ClientCounter::tableName(), 'sms_sum_day', $this->decimal(12, 2)->notNull()->defaultValue(0));
        $this->addColumn(ClientCounter::tableName(), 'sms_sum_month', $this->decimal(12, 2)->notNull()->defaultValue(0));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(ClientCounter::tableName(), 'voice_sum_day');
        $this->dropColumn(ClientCounter::tableName(), 'voice_sum_month');
        $this->dropColumn(ClientCounter::tableName(), 'data_sum_day');
        $this->dropColumn(ClientCounter::tableName(), 'data_sum_month');
        $this->dropColumn(ClientCounter::tableName(), 'sms_sum_day');
        $this->dropColumn(ClientCounter::tableName(), 'sms_sum_month');
    }
}
