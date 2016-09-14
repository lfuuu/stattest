<?php

use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\ServiceType;

class m160914_164618_sync_uu_voip extends \app\classes\Migration
{
    public function up()
    {
        $table = AccountTariff::tableName();

        $this->execute("
            CREATE TRIGGER `sync_{$table}_voip_insert` AFTER INSERT ON `{$table}` FOR EACH ROW BEGIN
                IF  NEW.service_type_id = " . ServiceType::ID_VOIP . " 
                    AND NEW.voip_number IS NOT NULL THEN
                    CALL z_sync_postgres('{$table}', NEW.id);
                END IF;
            END
        ");

        $this->execute("
            CREATE TRIGGER `sync_{$table}_voip_update` AFTER UPDATE ON `{$table}` FOR EACH ROW BEGIN
                IF  OLD.service_type_id = " . ServiceType::ID_VOIP . " 
                    AND OLD.voip_number IS NOT NULL 
                    AND IFNULL(OLD.tariff_period_id, 0) != IFNULL(NEW.tariff_period_id, 0) 
                THEN
                    CALL z_sync_postgres('{$table}', OLD.id);
                END IF;
            END
        ");

        $this->alterColumn('z_sync_postgres', "tname", "enum('clients','usage_voip','usage_voip_package','tarifs_voip','log_tarif','usage_trunk','usage_trunk_settings','organization','prefixlist','tariff_package','dest_prefixes','currency_rate', '{$table}') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL");

        //процедуры в инсталяторе нет
        $this->execute("DROP PROCEDURE IF EXISTS `z_sync_postgres`");
        $this->execute("
            CREATE PROCEDURE `z_sync_postgres`(IN p_table VARCHAR(20), IN p_id INTEGER(11))
            BEGIN
                DECLARE Continue HANDLER FOR 1062
                BEGIN
                    UPDATE z_sync_postgres SET rnd=RAND()*2000000000 WHERE tbase='nispd' and tname=p_table and tid=p_id;
                END;
                INSERT INTO z_sync_postgres(tbase, tname, tid, rnd) VALUES ('nispd', p_table, p_id, RAND()*2000000000);
            END
        ");
    }

    public function down()
    {
        $table = AccountTariff::tableName();

        $this->execute("DROP TRIGGER IF EXISTS `sync_{$table}_voip_insert`");
        $this->execute("DROP TRIGGER IF EXISTS `sync_{$table}_voip_update`");
        $this->alterColumn('z_sync_postgres', "tname", "enum('clients','usage_voip','usage_voip_package','tarifs_voip','log_tarif','usage_trunk','usage_trunk_settings','organization','prefixlist','tariff_package','dest_prefixes','currency_rate') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL");
        $this->execute("DROP PROCEDURE IF EXISTS `z_sync_postgres`");
    }
}