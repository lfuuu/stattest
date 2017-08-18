<?php

/**
 * Class m170817_160931_z_sync_clients
 */
class m170817_160931_z_sync_clients extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        // добавил price_include_vat
        $this->execute("DROP TRIGGER IF EXISTS to_postgres_clients_after_upd_tr");
        $sql = <<<SQL
            CREATE TRIGGER to_postgres_clients_after_upd_tr AFTER UPDATE ON clients FOR EACH ROW BEGIN
                if
                    NEW.voip_credit_limit_day <> OLD.voip_credit_limit_day
                    OR NEW.voip_limit_mn_day <> OLD.voip_limit_mn_day
                    OR NEW.voip_disabled <> OLD.voip_disabled
                    OR NEW.balance <> OLD.balance
                    OR NEW.credit <> OLD.credit
                    OR NEW.credit_mgp <> OLD.credit_mgp
                    OR NEW.is_blocked <> OLD.is_blocked
                    OR NEW.price_include_vat <> OLD.price_include_vat
                    OR NEW.effective_vat_rate <> OLD.effective_vat_rate
                    OR IFNULL(NEW.last_account_date, "2000-01-01") <> IFNULL(OLD.last_account_date, "2000-01-01")
                    OR IFNULL(NEW.last_payed_voip_month, "2000-01-01") <> IFNULL(OLD.last_payed_voip_month, "2000-01-01")
                THEN
                    call z_sync_postgres("clients", NEW.id);
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
        $this->execute("DROP TRIGGER IF EXISTS to_postgres_clients_after_upd_tr");
        $sql = <<<SQL
            CREATE TRIGGER to_postgres_clients_after_upd_tr AFTER UPDATE ON clients FOR EACH ROW BEGIN
                if
                    NEW.voip_credit_limit_day <> OLD.voip_credit_limit_day
                    OR NEW.voip_limit_mn_day <> OLD.voip_limit_mn_day
                    OR NEW.voip_disabled <> OLD.voip_disabled
                    OR NEW.balance <> OLD.balance
                    OR NEW.credit <> OLD.credit
                    OR NEW.credit_mgp <> OLD.credit_mgp
                    OR NEW.is_blocked <> OLD.is_blocked
                    OR NEW.effective_vat_rate <> OLD.effective_vat_rate
                    OR IFNULL(NEW.last_account_date, "2000-01-01") <> IFNULL(OLD.last_account_date, "2000-01-01")
                    OR IFNULL(NEW.last_payed_voip_month, "2000-01-01") <> IFNULL(OLD.last_payed_voip_month, "2000-01-01")
                THEN
                    call z_sync_postgres("clients", NEW.id);
                END IF;
            END;
SQL;
        $this->execute($sql);
    }
}
