<?php

use app\classes\Migration;
use app\modules\uu\models\AccountTariffChange;

/**
 * Class m230519_092616_uu_account_tariff_change_user
 */
class m230519_092616_uu_account_tariff_change_user extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(AccountTariffChange::tableName(), 'user_id', $this->integer());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(AccountTariffChange::tableName(), 'user_id');
    }
}
