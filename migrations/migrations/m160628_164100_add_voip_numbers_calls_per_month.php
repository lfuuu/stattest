<?php

class m160628_164100_add_voip_numbers_calls_per_month extends \app\classes\Migration
{
    public function up()
    {
        $tableName = \app\models\Number::tableName();
        $this->addColumn($tableName, 'calls_per_month_0', $this->integer());
        $this->addColumn($tableName, 'calls_per_month_1', $this->integer());
        $this->addColumn($tableName, 'calls_per_month_2', $this->integer());
    }

    public function down()
    {
        $tableName = \app\models\Number::tableName();
        $this->dropColumn($tableName, 'calls_per_month_0');
        $this->dropColumn($tableName, 'calls_per_month_1');
        $this->dropColumn($tableName, 'calls_per_month_2');
    }
}