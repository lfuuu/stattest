<?php

class m170816_102435_z_sync_uu extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        // использовать событие uu_account_tariff вместо uu_lines
        $this->execute("DROP TRIGGER IF EXISTS uu_account_tariff_resource_log_after_upd_tr");
        $sql = <<<SQL
            CREATE TRIGGER uu_account_tariff_resource_log_after_upd_tr AFTER UPDATE ON uu_account_tariff_resource_log FOR EACH ROW BEGIN
                if
                    OLD.sync_time IS NULL
                    AND NEW.sync_time IS NOT NULL
                    AND NEW.resource_id = 7
                THEN
                    call z_sync_postgres("uu_account_tariff", NEW.account_tariff_id);
                END IF;
            END;
SQL;
        $this->execute($sql);


        // Убрал лишнюю проверку
        $this->execute("DROP TRIGGER IF EXISTS sync_uu_account_tariff_voip_insert");
        $sql = <<<SQL
            CREATE TRIGGER `sync_uu_account_tariff_voip_insert` AFTER INSERT ON `uu_account_tariff` FOR EACH ROW BEGIN
                IF  NEW.service_type_id = 2 
                    AND NEW.voip_number IS NOT NULL THEN
                    CALL z_sync_postgres('uu_account_tariff', NEW.id);
                END IF;
            END;
SQL;
        $this->execute($sql);

        // Убрал лишнюю проверку
        $this->execute("DROP TRIGGER IF EXISTS sync_uu_account_tariff_voip_update");
        $sql = <<<SQL
            CREATE TRIGGER `sync_uu_account_tariff_voip_update` AFTER UPDATE ON `uu_account_tariff` FOR EACH ROW BEGIN
                IF  OLD.service_type_id = 2 
                THEN
                    CALL z_sync_postgres('uu_account_tariff', OLD.id);
                END IF;
            END;
SQL;
        $this->execute($sql);

        // Убрал лишнюю проверку
        $this->execute("DROP TRIGGER IF EXISTS sync_uu_account_tariff_voip_delete");
        $sql = <<<SQL
            CREATE TRIGGER `sync_uu_account_tariff_voip_delete` AFTER DELETE ON `uu_account_tariff` FOR EACH ROW BEGIN
                IF  OLD.service_type_id = 2 
                THEN
                    CALL z_sync_postgres('uu_account_tariff', OLD.id);
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
        $this->execute("DROP TRIGGER IF EXISTS uu_account_tariff_resource_log_after_upd_tr");
        $sql = <<<SQL
            CREATE TRIGGER uu_account_tariff_resource_log_after_upd_tr AFTER UPDATE ON uu_account_tariff_resource_log FOR EACH ROW BEGIN
                if
                    OLD.sync_time IS NULL
                    AND NEW.sync_time IS NOT NULL
                    AND NEW.resource_id = 7
                THEN
                    call z_sync_postgres("uu_lines", NEW.id);
                END IF;
            END;
SQL;
        $this->execute($sql);

        $this->execute("DROP TRIGGER IF EXISTS sync_uu_account_tariff_voip_insert");
        $sql = <<<SQL
            CREATE TRIGGER `sync_uu_account_tariff_voip_insert` AFTER INSERT ON `uu_account_tariff` FOR EACH ROW BEGIN
                IF  NEW.service_type_id = 2 
                    AND NEW.id >= 100000
                    AND NEW.voip_number IS NOT NULL THEN
                    CALL z_sync_postgres('uu_account_tariff', NEW.id);
                END IF;
            END;
SQL;
        $this->execute($sql);

        $this->execute("DROP TRIGGER IF EXISTS sync_uu_account_tariff_voip_update");
        $sql = <<<SQL
            CREATE TRIGGER `sync_uu_account_tariff_voip_update` AFTER UPDATE ON `uu_account_tariff` FOR EACH ROW BEGIN
                IF  OLD.service_type_id = 2 
                    AND OLD.id >= 100000
                THEN
                    CALL z_sync_postgres('uu_account_tariff', OLD.id);
                END IF;
            END;
SQL;
        $this->execute($sql);

        $this->execute("DROP TRIGGER IF EXISTS sync_uu_account_tariff_voip_delete");
        $sql = <<<SQL
            CREATE TRIGGER `sync_uu_account_tariff_voip_delete` AFTER DELETE ON `uu_account_tariff` FOR EACH ROW BEGIN
                IF  OLD.service_type_id = 2 
                    AND OLD.id >= 100000
                THEN
                    CALL z_sync_postgres('uu_account_tariff', OLD.id);
                END IF;
            END;
SQL;
        $this->execute($sql);
    }
}
