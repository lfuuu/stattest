<?php

/**
 * Class m190225_101934_sync_voip_numbers_update
 */
class m190225_101934_sync_voip_numbers_update extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {

        $this->alterColumn(\app\models\SyncPostgres::tableName(), 'tid', $this->bigInteger(20)->notNull());
        $this->execute('DROP TRIGGER IF EXISTS `sync_voip_numbers_update`');

        $sql = <<<SQL
CREATE TRIGGER `sync_voip_numbers_update` AFTER UPDATE ON `voip_numbers`
FOR EACH ROW BEGIN
            
                IF  COALESCE(OLD.number_tech, '') <> COALESCE(NEW.number_tech, '')
                    OR COALESCE(OLD.operator_account_id, '') <> COALESCE(NEW.operator_account_id, '')
                    OR COALESCE(OLD.fmc_trunk_id, '') <> COALESCE(NEW.fmc_trunk_id, '')
                    OR COALESCE(OLD.mvno_trunk_id, '') <> COALESCE(NEW.mvno_trunk_id, '')
                    OR COALESCE(OLD.usage_id, '') <> COALESCE(NEW.usage_id, '')
                    OR COALESCE(OLD.uu_account_tariff_id, '') <> COALESCE(NEW.uu_account_tariff_id, '')
                    OR COALESCE(OLD.status, '') <> COALESCE(NEW.status, '')
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
                
            END;
SQL;
        $this->execute($sql);

        $this->execute('DROP PROCEDURE IF EXISTS `z_sync_postgres`');
        $sql = <<<SQL
CREATE PROCEDURE `z_sync_postgres`(IN p_table VARCHAR(20), IN p_id BIGINT(20))
BEGIN
          DECLARE Continue HANDLER FOR 1062
          BEGIN
            UPDATE z_sync_postgres SET rnd=RAND()*2000000000 WHERE tbase='nispd' and tname=p_table and tid=p_id;
          END;
          INSERT INTO z_sync_postgres(tbase, tname, tid, rnd) VALUES ('nispd', p_table, p_id, RAND()*2000000000);
        END
SQL;
        $this->execute($sql);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(\app\models\SyncPostgres::tableName(), 'tid', $this->integer(11)->notNull());

        $this->execute('DROP TRIGGER IF EXISTS `sync_voip_numbers_update`');
        $sql = <<<SQL
CREATE TRIGGER `sync_voip_numbers_update` AFTER UPDATE ON `voip_numbers`
FOR EACH ROW BEGIN
            
                IF  COALESCE(OLD.number_tech, '') <> COALESCE(NEW.number_tech, '')
                    OR COALESCE(OLD.operator_account_id, '') <> COALESCE(NEW.operator_account_id, '')
                    OR COALESCE(OLD.fmc_trunk_id, '') <> COALESCE(NEW.fmc_trunk_id, '')
                    OR COALESCE(OLD.mvno_trunk_id, '') <> COALESCE(NEW.mvno_trunk_id, '')
                    OR COALESCE(OLD.usage_id, '') <> COALESCE(NEW.usage_id, '')
                    OR COALESCE(OLD.uu_account_tariff_id, '') <> COALESCE(NEW.uu_account_tariff_id, '')
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
                    
                END IF;


								IF OLD.status <> "instock" AND NEW.status = "instock" THEN
                    call add_event("export_free_number_free", NEW.number);
                ELSEIF OLD.status = "instock" AND NEW.status <> "instock" THEN
                    call add_event("export_free_number_busy", NEW.number);
                END IF;
                
            END;
SQL;
        $this->execute($sql);

        $this->execute('DROP PROCEDURE IF EXISTS `z_sync_postgres`');
        $sql = <<<SQL
CREATE PROCEDURE `z_sync_postgres`(IN p_table VARCHAR(20), IN p_id INTEGER(11))
BEGIN
          DECLARE Continue HANDLER FOR 1062
          BEGIN
            UPDATE z_sync_postgres SET rnd=RAND()*2000000000 WHERE tbase='nispd' and tname=p_table and tid=p_id;
          END;
          INSERT INTO z_sync_postgres(tbase, tname, tid, rnd) VALUES ('nispd', p_table, p_id, RAND()*2000000000);
        END
SQL;
        $this->execute($sql);


    }
}
