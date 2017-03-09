<?php

/**
 * Class m170302_144021_client_add_account_to_behaviors
 */
class m170302_144021_client_add_account_to_behaviors extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->execute("DROP TRIGGER IF EXISTS `to_postgres_clients_after_ins_tr`");
        $SQL = <<<SQL
CREATE TRIGGER `to_postgres_clients_after_ins_tr` AFTER INSERT ON `clients` FOR EACH ROW BEGIN
     call z_sync_postgres('clients', NEW.id);
END;
SQL;
        $this->execute($SQL);

        $this->execute('DROP PROCEDURE IF EXISTS `z_sync_postgres`');
        $SQL = <<<SQL
CREATE PROCEDURE `z_sync_postgres`(IN p_table VARCHAR(20), IN p_id INTEGER(11))
BEGIN
    DECLARE Continue HANDLER FOR 1062
    BEGIN
        UPDATE z_sync_postgres SET rnd=RAND()*2000000000 WHERE tbase='nispd' and tname=p_table and tid=p_id;
    END;

    INSERT INTO z_sync_postgres(tbase, tname, tid, rnd) VALUES ('nispd', p_table, p_id, RAND()*2000000000);
END;
SQL;
        $this->execute($SQL);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->execute("DROP TRIGGER IF EXISTS `to_postgres_clients_after_ins_tr`");

        $SQL = <<<SQL
CREATE TRIGGER `to_postgres_clients_after_ins_tr` AFTER INSERT ON `clients` FOR EACH ROW BEGIN
     call z_sync_postgres('clients', NEW.id);

     call add_event('add_account', NEW.id);
END;
SQL;
        $this->execute($SQL);
    }
}
