<?php

/**
 * Class m170817_162712_z_sync_uu_account_tariff_log
 */
class m170817_162712_z_sync_uu_account_tariff_log extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $sql = <<<SQL
            CREATE TRIGGER `sync_uu_account_tariff_log_insert` AFTER INSERT ON `uu_account_tariff_log` FOR EACH ROW BEGIN
                IF  NEW.tariff_period_id IS NULL
                -- todo service_type_id = 2 AND voip_number IS NOT NULL
                THEN 
                    CALL z_sync_postgres('uu_account_tariff', NEW.account_tariff_id);
                END IF;
            END;
SQL;
        $this->execute($sql);

        $sql = <<<SQL
            CREATE TRIGGER `sync_uu_account_tariff_log_delete` AFTER DELETE ON `uu_account_tariff_log` FOR EACH ROW BEGIN
                IF  OLD.tariff_period_id IS NULL 
                -- todo service_type_id = 2 AND voip_number IS NOT NULL
                THEN
                    CALL z_sync_postgres('uu_account_tariff', OLD.account_tariff_id);
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
        $this->execute("DROP TRIGGER IF EXISTS sync_uu_account_tariff_log_insert");
        $this->execute("DROP TRIGGER IF EXISTS sync_uu_account_tariff_log_delete");
    }
}
