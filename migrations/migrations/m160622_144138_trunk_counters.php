<?php

use app\models\CounterInteropTrunk;

class m160622_144138_trunk_counters extends \app\classes\Migration
{
    public function up()
    {
        $this->createTable(CounterInteropTrunk::tableName(), [
            'account_id' => $this->primaryKey(),
            'income_sum' => $this->decimal(12,2),
            'outcome_sum' => $this->decimal(12,2),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    }

    public function down()
    {
        $this->dropTable(CounterInteropTrunk::tableName());
    }
}