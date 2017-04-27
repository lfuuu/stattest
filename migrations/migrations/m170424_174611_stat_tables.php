<?php
use app\models\voip\StatisticDay;
use app\models\voip\StatisticMonth;

/**
 * Class m170424_174611_stat_tables
 */
class m170424_174611_stat_tables extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(StatisticDay::tableName(), [
            'account_id' => $this->integer()->notNull(),
            'date' => $this->date()->notNull(),
            'count' => $this->integer()->notNull(),
            'cost' => $this->float()->notNull()
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $this->addPrimaryKey('pk-account_id-date', StatisticDay::tableName(), ['account_id', 'date']);

        $this->createTable(StatisticMonth::tableName(), [
            'account_id' => $this->integer()->notNull(),
            'date' => $this->date()->notNull(),
            'count' => $this->integer()->notNull(),
            'cost' => $this->float()->notNull(),
            'average_cost' => $this->float()->notNull(),
            'days_with_calls' => $this->integer()->notNull(),
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $this->addPrimaryKey('pk-account_id-date', StatisticMonth::tableName(), ['account_id', 'date']);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(StatisticDay::tableName());
        $this->dropTable(StatisticMonth::tableName());
    }
}
