<?php

/**
 * Class m240806_130711_voip_number_calls_3
 */
class m240806_130711_voip_number_calls_3 extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\models\Number::tableName(), 'calls_per_month_3', $this->integer()->after('calls_per_month_2'));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\models\Number::tableName(), 'calls_per_month_3');
    }
}
