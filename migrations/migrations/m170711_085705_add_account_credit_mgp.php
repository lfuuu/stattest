<?php
use app\models\ClientAccount;

/**
 * Class m170711_085705_add_account_credit_mgp
 */
class m170711_085705_add_account_credit_mgp extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $tableName = ClientAccount::tableName();
        $this->addColumn($tableName, 'credit_mgp', $this->integer()->notNull()->defaultValue(0));

        $this->execute('DROP TRIGGER IF EXISTS to_postgres_clients_after_upd_tr');
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

        // в тестовой базе нет процедуры z_sync_postgres, поэтому пересоздадим ее, чтобы тесты не падали
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
     * Down
     */
    public function safeDown()
    {
        $tableName = ClientAccount::tableName();
        $this->dropColumn($tableName, 'credit_mgp');

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
    }
}
