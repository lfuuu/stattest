<?php

use app\modules\uu\models\AccountTariff;

class m180318_164702_add_uu_account_tariff_account_log_resource_utc extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(AccountTariff::tableName(), 'account_log_resource_utc', $this->dateTime());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(AccountTariff::tableName(), 'account_log_resource_utc');
    }
}
