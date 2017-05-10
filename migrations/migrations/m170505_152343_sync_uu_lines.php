<?php
use app\modules\uu\models\AccountTariffResourceLog;

/**
 * Class m170505_152343_sync_uu_lines
 */
class m170505_152343_sync_uu_lines extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $tableName = AccountTariffResourceLog::tableName();
        $this->alterColumn(
            'z_sync_postgres',
            'tname',
            "enum('clients', 'usage_voip', 'usage_voip_package', 'tarifs_voip', 'log_tarif', 'usage_trunk', 'usage_trunk_settings', 'organization', 'prefixlist', 'tariff_package', 'dest_prefixes', 'currency_rate', 'uu_account_tariff', 'uu_lines')"
        );


        $resourceIdVoipLine = \app\modules\uu\models\Resource::ID_VOIP_LINE;
        // только после отправки на платформу данных о количестве линий
        $sql = <<<SQL
        CREATE TRIGGER {$tableName}_after_upd_tr AFTER UPDATE ON {$tableName} FOR EACH ROW BEGIN
                if
                    OLD.sync_time IS NULL
                    AND NEW.sync_time IS NOT NULL
                    AND NEW.resource_id = {$resourceIdVoipLine}
                THEN
                    call z_sync_postgres("uu_lines", NEW.id);
                END IF;
            END;

SQL;
        $this->execute($sql);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $tableName = AccountTariffResourceLog::tableName();
        $this->execute("DROP TRIGGER IF EXISTS {$tableName}_after_upd_tr");

        $this->alterColumn(
            'z_sync_postgres',
            'tname',
            "enum('clients', 'usage_voip', 'usage_voip_package', 'tarifs_voip', 'log_tarif', 'usage_trunk', 'usage_trunk_settings', 'organization', 'prefixlist', 'tariff_package', 'dest_prefixes', 'currency_rate', 'uu_account_tariff')"
        );
    }
}
