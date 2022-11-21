<?php

/**
 * Class m221121_121114_add_currencies
 */
class m221121_121114_add_currencies extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->batchInsert(\app\models\Currency::tableName(), ['id', 'name', 'symbol', 'code'], [
            ['CAD', 'Канадский доллар', 'C$', 124],
            ['GBP', 'Фунт стерлингов', '£', 826],
            ['KZT', 'Казахский тенге', '₸', 398],
            ['RSD', 'Сербский динар', 'RSD', 941],
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->delete(\app\models\Currency::tableName(), ['code' => [124, 826, 398, 941]]);
    }
}
