<?php
use app\models\SyncPostgres;

/**
 * Class m170801_145752_z_sync_subaccount
 */
class m170801_145752_z_sync_subaccount extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {

        $this->alterColumn(SyncPostgres::tableName(), 'tname', $this->string());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(SyncPostgres::tableName(), 'tname', "enum('clients','usage_voip','usage_voip_package','tarifs_voip','log_tarif','usage_trunk','usage_trunk_settings','organization','prefixlist','tariff_package','dest_prefixes','currency_rate','uu_account_tariff','uu_lines') NOT NULL DEFAULT 'clients'");
    }
}
