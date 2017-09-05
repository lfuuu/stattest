<?php

/**
 * Class m170904_095726_sync_voip_numbers
 */
class m170904_095726_sync_voip_numbers extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        // добавил usage_id, uu_account_tariff_id
        // добавил elseif old
        $this->execute("DROP TRIGGER IF EXISTS sync_voip_numbers_update");
        $sql = <<<SQL
            CREATE TRIGGER `sync_voip_numbers_update` AFTER UPDATE ON `voip_numbers` FOR EACH ROW BEGIN
            
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
                
            END
SQL;
        $this->execute($sql);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->execute("DROP TRIGGER IF EXISTS sync_voip_numbers_update");
        $sql = <<<SQL
            CREATE TRIGGER `sync_voip_numbers_update` AFTER UPDATE ON `voip_numbers` FOR EACH ROW BEGIN
            
                IF  COALESCE(OLD.number_tech, '') <> COALESCE(NEW.number_tech, '')
                    OR COALESCE(OLD.operator_account_id, '') <> COALESCE(NEW.operator_account_id, '')
                    OR COALESCE(OLD.fmc_trunk_id, '') <> COALESCE(NEW.fmc_trunk_id, '')
                    OR COALESCE(OLD.mvno_trunk_id, '') <> COALESCE(NEW.mvno_trunk_id, '')
                THEN
                
                    IF  NEW.usage_id IS NOT NULL 
                    THEN
                        CALL z_sync_postgres('usage_voip', NEW.usage_id);
                    END IF;
                    
                    IF NEW.uu_account_tariff_id IS NOT NULL 
                    THEN
                        CALL z_sync_postgres('uu_account_tariff', NEW.uu_account_tariff_id);
                    END IF;
                    
                END IF;
                
            END
SQL;
        $this->execute($sql);
    }
}
