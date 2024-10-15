<?php

use app\models\Number;

/**
 * Class m241009_131756_unique_calls
 */
class m241009_131756_unique_calls extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Number::tableName(), 'unique_calls_per_month_0', $this->integer());
        $this->addColumn(Number::tableName(), 'unique_calls_per_month_1', $this->integer());
        $this->addColumn(Number::tableName(), 'unique_calls_per_month_2', $this->integer());
        $this->addColumn(Number::tableName(), 'unique_calls_per_month_3', $this->integer());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Number::tableName(), 'unique_calls_per_month_0');
        $this->dropColumn(Number::tableName(), 'unique_calls_per_month_1');
        $this->dropColumn(Number::tableName(), 'unique_calls_per_month_2');
        $this->dropColumn(Number::tableName(), 'unique_calls_per_month_3');
    }
}
