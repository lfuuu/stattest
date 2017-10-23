<?php

use app\modules\uu\models\AccountTariff;

/**
 * Class m171020_143943_add_account_tariff_mtt
 */
class m171020_143943_add_account_tariff_mtt extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(AccountTariff::tableName(), 'mtt_number', $this->string(255));
        $this->addColumn(AccountTariff::tableName(), 'mtt_balance', $this->decimal(13, 4));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(AccountTariff::tableName(), 'mtt_number');
        $this->dropColumn(AccountTariff::tableName(), 'mtt_balance');
    }
}
