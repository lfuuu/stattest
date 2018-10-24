<?php

use app\modules\uu\models\AccountTariffFlat;

/**
 * Class m181011_100004_updates_to_uu_account_tariff_flat
 */
class m181011_100004_updates_to_uu_account_tariff_flat extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $table = AccountTariffFlat::tableName();
        $this->renameTable('uu_account_tariff_voip_flat', $table);
        $this->addColumn($table, 'service_type', $this->string());
        $this->addColumn($table, 'is_unzipped', $this->boolean());
        $this->addColumn($table, 'prev_account_tariff_tariff', $this->string());
        $this->addColumn($table, 'lead', $this->string());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $table = 'uu_account_tariff_voip_flat';
        $this->renameTable(AccountTariffFlat::tableName(), $table);
        $this->dropColumn($table, 'service_type');
        $this->dropColumn($table, 'is_unzipped');
        $this->dropColumn($table, 'prev_account_tariff_tariff');
        $this->dropColumn($table, 'lead');
    }
}
