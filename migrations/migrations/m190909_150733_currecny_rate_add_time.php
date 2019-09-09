<?php

use app\models\CurrencyRate;

/**
 * Class m190909_150733_currecny_rate_add_time
 */
class m190909_150733_currecny_rate_add_time extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(CurrencyRate::tableName(), 'created_at', $this->dateTime());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(CurrencyRate::tableName(), 'created_at');
    }
}
