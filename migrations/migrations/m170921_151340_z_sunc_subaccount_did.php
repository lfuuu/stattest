<?php

/**
 * Class m170921_151340_z_sunc_subaccount_did
 */
class m170921_151340_z_sunc_subaccount_did extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE TRIGGER `to_postgres_client_subaccount_after_update` AFTER UPDATE ON `client_subaccount` FOR EACH ROW BEGIN
                if
                    NEW.account_id <> OLD.account_id
                    OR NEW.sub_account <> OLD.sub_account
                    OR NEW.number <> OLD.number
                    OR NEW.stat_product_id <> OLD.stat_product_id
                    OR NEW.balance <> OLD.balance
                    OR NEW.credit <> OLD.credit
                    OR NEW.amount_date <> OLD.amount_date
                    OR NEW.voip_limit_month <> OLD.voip_limit_month
                    OR NEW.voip_limit_day <> OLD.voip_limit_day
                    OR NEW.voip_limit_mn_day <> OLD.voip_limit_mn_day
                    OR NEW.voip_limit_mn_month <> OLD.voip_limit_mn_month
                    OR NEW.is_voip_orig_disabled <> OLD.is_voip_orig_disabled
                    OR NEW.is_voip_blocked <> OLD.is_voip_blocked
                    OR COALESCE(NEW.did, '') <> COALESCE(OLD.did, '')
                THEN
                    call z_sync_postgres("client_subaccount", NEW.id);
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
        $sql = <<<SQL
CREATE TRIGGER `to_postgres_client_subaccount_after_update` AFTER UPDATE ON `client_subaccount` FOR EACH ROW BEGIN
                if
                    NEW.account_id <> OLD.account_id
                    OR NEW.sub_account <> OLD.sub_account
                    OR NEW.number <> OLD.number
                    OR NEW.stat_product_id <> OLD.stat_product_id
                    OR NEW.balance <> OLD.balance
                    OR NEW.credit <> OLD.credit
                    OR NEW.amount_date <> OLD.amount_date
                    OR NEW.voip_limit_month <> OLD.voip_limit_month
                    OR NEW.voip_limit_day <> OLD.voip_limit_day
                    OR NEW.voip_limit_mn_day <> OLD.voip_limit_mn_day
                    OR NEW.voip_limit_mn_month <> OLD.voip_limit_mn_month
                    OR NEW.is_voip_orig_disabled <> OLD.is_voip_orig_disabled
                    OR NEW.is_voip_blocked <> OLD.is_voip_blocked
                THEN
                    call z_sync_postgres("client_subaccount", NEW.id);
                END IF;
            END;
SQL;
        $this->execute($sql);
    }
}
