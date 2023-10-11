<?php

/**
 * Class m231011_142024_voip_number_trigger_nnp
 */
class m231011_142024_voip_number_trigger_nnp extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->executeRaw('DROP TRIGGER IF EXISTS sync_voip_numbers_update');
        $sql = <<<SQL
CREATE TRIGGER `sync_voip_numbers_update` AFTER UPDATE ON `voip_numbers` FOR EACH ROW BEGIN

    IF  COALESCE(OLD.number_tech, '') <> COALESCE(NEW.number_tech, '')
        OR COALESCE(OLD.operator_account_id, '') <> COALESCE(NEW.operator_account_id, '')
        OR COALESCE(OLD.fmc_trunk_id, '') <> COALESCE(NEW.fmc_trunk_id, '')
        OR COALESCE(OLD.mvno_partner_id, '') <> COALESCE(NEW.mvno_partner_id, '')
        OR COALESCE(OLD.usage_id, '') <> COALESCE(NEW.usage_id, '')
        OR COALESCE(OLD.uu_account_tariff_id, '') <> COALESCE(NEW.uu_account_tariff_id, '')
        OR COALESCE(OLD.status, '') <> COALESCE(NEW.status, '')
        OR COALESCE(OLD.source, '') <> COALESCE(NEW.source, '')
        OR COALESCE(OLD.mvno_trunk_id, '') <> COALESCE(NEW.mvno_trunk_id, '')
        OR COALESCE(OLD.nnp_city_id, '') <> COALESCE(NEW.nnp_city_id, '')
        OR COALESCE(OLD.nnp_region_id, '') <> COALESCE(NEW.nnp_region_id, '')
        OR COALESCE(OLD.nnp_operator_id, '') <> COALESCE(NEW.nnp_operator_id, '')
    THEN

        IF  NEW.usage_id IS NOT NULL THEN
            CALL z_sync_postgres('usage_voip', NEW.usage_id);
        ELSEIF OLD.usage_id IS NOT NULL THEN
            CALL z_sync_postgres('usage_voip', OLD.usage_id);
        END IF;

        IF NEW.uu_account_tariff_id IS NOT NULL THEN
            CALL z_sync_postgres('uu_account_tariff', NEW.uu_account_tariff_id);
        ELSEIF OLD.uu_account_tariff_id IS NOT NULL THEN
            CALL z_sync_postgres('uu_account_tariff', OLD.uu_account_tariff_id);
        END IF;

        CALL z_sync_postgres('voip_number', OLD.number);

    END IF;


    IF OLD.status <> "instock" AND NEW.status = "instock" THEN
        call add_event("export_free_number_free", NEW.number);
    ELSEIF OLD.status = "instock" AND NEW.status <> "instock" THEN
        call add_event("export_free_number_busy", NEW.number);
    END IF;

END
SQL;

        $this->executeRaw($sql);

    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->executeRaw('DROP TRIGGER IF EXISTS sync_voip_numbers_update');
        $sql = <<<SQL
CREATE TRIGGER `sync_voip_numbers_update` AFTER UPDATE ON `voip_numbers` FOR EACH ROW BEGIN

    IF  COALESCE(OLD.number_tech, '') <> COALESCE(NEW.number_tech, '')
        OR COALESCE(OLD.operator_account_id, '') <> COALESCE(NEW.operator_account_id, '')
        OR COALESCE(OLD.fmc_trunk_id, '') <> COALESCE(NEW.fmc_trunk_id, '')
        OR COALESCE(OLD.mvno_partner_id, '') <> COALESCE(NEW.mvno_partner_id, '')
        OR COALESCE(OLD.usage_id, '') <> COALESCE(NEW.usage_id, '')
        OR COALESCE(OLD.uu_account_tariff_id, '') <> COALESCE(NEW.uu_account_tariff_id, '')
        OR COALESCE(OLD.status, '') <> COALESCE(NEW.status, '')
        OR COALESCE(OLD.source, '') <> COALESCE(NEW.source, '')
        OR COALESCE(OLD.mvno_trunk_id, '') <> COALESCE(NEW.mvno_trunk_id, '')
    THEN

        IF  NEW.usage_id IS NOT NULL THEN
            CALL z_sync_postgres('usage_voip', NEW.usage_id);
        ELSEIF OLD.usage_id IS NOT NULL THEN
            CALL z_sync_postgres('usage_voip', OLD.usage_id);
        END IF;

        IF NEW.uu_account_tariff_id IS NOT NULL THEN
            CALL z_sync_postgres('uu_account_tariff', NEW.uu_account_tariff_id);
        ELSEIF OLD.uu_account_tariff_id IS NOT NULL THEN
            CALL z_sync_postgres('uu_account_tariff', OLD.uu_account_tariff_id);
        END IF;

        CALL z_sync_postgres('voip_number', OLD.number);

    END IF;


    IF OLD.status <> "instock" AND NEW.status = "instock" THEN
        call add_event("export_free_number_free", NEW.number);
    ELSEIF OLD.status = "instock" AND NEW.status <> "instock" THEN
        call add_event("export_free_number_busy", NEW.number);
    END IF;

END
SQL;

        $this->executeRaw($sql);
    }
}
