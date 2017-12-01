<?php

use app\modules\uu\models\AccountTariff;

/**
 * Class m171201_102703_alter_infrastructure
 */
class m171201_102703_alter_infrastructure extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $accountTariffTableName = AccountTariff::tableName();
        $this->alterColumn($accountTariffTableName, 'price', $this->decimal(13, 2));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $accountTariffTableName = AccountTariff::tableName();
        $this->alterColumn($accountTariffTableName, 'price', $this->integer());
    }
}
