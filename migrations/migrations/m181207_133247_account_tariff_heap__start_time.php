<?php

use app\modules\uu\models\AccountTariffHeap;

/**
 * Class m181207_133247_account_tariff_heap__start_time
 */
class m181207_133247_account_tariff_heap__start_time extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(AccountTariffHeap::tableName(), 'start_date', $this->dateTime());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(AccountTariffHeap::tableName(), 'start_date');
    }
}
