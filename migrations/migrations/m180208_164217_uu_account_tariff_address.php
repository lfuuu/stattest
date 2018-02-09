<?php

use app\modules\uu\models\AccountTariff;

/**
 * Class m180208_164217_uu_account_tariff_address
 */
class m180208_164217_uu_account_tariff_address extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(AccountTariff::tableName(), 'device_address', $this->string(1024)->notNull()->defaultValue(''));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(AccountTariff::tableName(), 'device_address');
    }
}
