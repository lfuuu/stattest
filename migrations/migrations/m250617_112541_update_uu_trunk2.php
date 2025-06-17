<?php

/**
 * Class m250617_112541_update_uu_trunk2
 */
class m250617_112541_update_uu_trunk2 extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->execute('DROP TRIGGER `sync_uu_account_tariff_voip_update`;');
        $sql = <<<SQL
CREATE TRIGGER `sync_uu_account_tariff_voip_update` AFTER UPDATE ON `uu_account_tariff` FOR EACH ROW BEGIN
    IF  OLD.service_type_id = 2 THEN
        CALL z_sync_postgres('uu_account_tariff', OLD.id);
    END IF;
    IF  OLD.service_type_id = 32 THEN
        CALL z_sync_postgres('service_api', OLD.id);
    END IF;
    IF  OLD.service_type_id = 35 THEN
        CALL z_sync_postgres('service_a2p', OLD.id);
    END IF;
    IF  OLD.service_type_id = 22 THEN
        CALL z_sync_postgres('usage_trunk', OLD.id);
        CALL z_sync_postgres('ut_calligrapher_tnl', OLD.id);
    END IF;
END
SQL;
        $this->execute($sql);

    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->execute('DROP TRIGGER `sync_uu_account_tariff_voip_update`;');
        $sql = <<<SQL
CREATE TRIGGER `sync_uu_account_tariff_voip_update` AFTER UPDATE ON `uu_account_tariff` FOR EACH ROW BEGIN
    IF  OLD.service_type_id = 2 THEN
        CALL z_sync_postgres('uu_account_tariff', OLD.id);
    END IF;
    IF  OLD.service_type_id = 32 THEN
        CALL z_sync_postgres('service_api', OLD.id);
    END IF;
    IF  OLD.service_type_id = 35 THEN
        CALL z_sync_postgres('service_a2p', OLD.id);
    END IF;
    IF  OLD.service_type_id = 22 THEN
        CALL z_sync_postgres('usage_trunk', OLD.id);
    END IF;
END
SQL;
        $this->execute($sql);

    }
}
