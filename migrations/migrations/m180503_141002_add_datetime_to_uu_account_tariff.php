<?php

use app\modules\uu\models\AccountTariff;

/**
 * Class m180503_141002_add_datetime_to_uu_account_tariff
 */
class m180503_141002_add_datetime_to_uu_account_tariff extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $accountTariffTableName = AccountTariff::tableName();
        $this->addColumn($accountTariffTableName, 'test_connect_date', $this->dateTime());
        $this->addColumn($accountTariffTableName, 'disconnect_date', $this->dateTime());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $accountTariffTableName = AccountTariff::tableName();
        $this->dropColumn($accountTariffTableName, 'test_connect_date');
        $this->dropColumn($accountTariffTableName, 'disconnect_date');
    }
}