<?php

class m170816_102436_z_sync_client_contract extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        // синхронизировать organization_id для clients
        $sql = <<<SQL
            CREATE TRIGGER sync_client_contract_update AFTER UPDATE ON client_contract FOR EACH ROW BEGIN

                DECLARE client_id INT;
                DECLARE done INT DEFAULT FALSE;
                
                DECLARE client_cursor CURSOR FOR SELECT id FROM clients WHERE contract_id = NEW.id;
                DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

                IF
                    NEW.organization_id <> OLD.organization_id
                THEN
                
                    OPEN client_cursor;
                    
                    WHILE NOT done DO 
                        FETCH client_cursor INTO client_id;
                        call z_sync_postgres("clients", client_id);
                    END WHILE;
                    
                    CLOSE client_cursor;
                      
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
        $this->execute("DROP TRIGGER IF EXISTS sync_client_contract_update");
    }
}
