<?php

use app\models\ClientAccount;

/**
 * Handles the dropping column `clients`.`voip_credit_limit`
 */
class m170227_173906_drop_field_clients_voip_limit extends \app\classes\Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->dropColumn(ClientAccount::tableName(), 'voip_credit_limit');

        $this->execute('DROP TRIGGER IF EXISTS to_postgres_clients_after_upd_tr');

        $sql = <<<SQL
        CREATE TRIGGER to_postgres_clients_after_upd_tr AFTER UPDATE ON clients FOR EACH ROW BEGIN
                if
                    NEW.voip_credit_limit_day <> OLD.voip_credit_limit_day
                    OR NEW.voip_limit_mn_day <> OLD.voip_limit_mn_day
                    OR NEW.voip_disabled <> OLD.voip_disabled
                    OR NEW.balance <> OLD.balance
                    OR NEW.credit <> OLD.credit
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

        $this->execute("DROP PROCEDURE IF EXISTS `z_sync_postgres`");

        $sql = <<<SQL
        CREATE PROCEDURE `z_sync_postgres`(IN p_table VARCHAR(20), IN p_id INTEGER(11))
        BEGIN
          DECLARE Continue HANDLER FOR 1062
          BEGIN
            UPDATE z_sync_postgres SET rnd=RAND()*2000000000 WHERE tbase='nispd' and tname=p_table and tid=p_id;
          END;
          INSERT INTO z_sync_postgres(tbase, tname, tid, rnd) VALUES ('nispd', p_table, p_id, RAND()*2000000000);
        END;
SQL;
        $this->execute($sql);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->addColumn(ClientAccount::tableName(), 'voip_credit_limit', $this->integer()->notNull()->defaultValue(0));

        $this->execute('DROP TRIGGER IF EXISTS to_postgres_clients_after_upd_tr');

        $sql = <<<SQL
        CREATE TRIGGER to_postgres_clients_after_upd_tr AFTER UPDATE ON clients FOR EACH ROW BEGIN
                if
                    NEW.voip_credit_limit <> OLD.voip_credit_limit
                    OR NEW.voip_credit_limit_day <> OLD.voip_credit_limit_day
                    OR NEW.voip_limit_mn_day <> OLD.voip_limit_mn_day
                    OR NEW.voip_disabled <> OLD.voip_disabled
                    OR NEW.balance <> OLD.balance
                    OR NEW.credit <> OLD.credit
                    OR NEW.is_blocked <> OLD.is_blocked
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
