<?php

use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\ServiceType;

class m160919_164618_sync_uu_voip extends \app\classes\Migration
{
    /**
     * Передавать в низкоуровневый биллинг только новые услуги, а не сконвертированные из старых
     */
    public function up()
    {
        $table = AccountTariff::tableName();

        $this->execute("DROP TRIGGER IF EXISTS `sync_{$table}_voip_insert`");
        $this->execute("
            CREATE TRIGGER `sync_{$table}_voip_insert` AFTER INSERT ON `{$table}` FOR EACH ROW BEGIN
                IF  NEW.service_type_id = " . ServiceType::ID_VOIP . " 
                    AND NEW.id >= " . AccountTariff::DELTA . "
                    AND NEW.voip_number IS NOT NULL THEN
                    CALL z_sync_postgres('{$table}', NEW.id);
                END IF;
            END
        ");

        $this->execute("DROP TRIGGER IF EXISTS `sync_{$table}_voip_update`");
        $this->execute("
            CREATE TRIGGER `sync_{$table}_voip_update` AFTER UPDATE ON `{$table}` FOR EACH ROW BEGIN
                IF  OLD.service_type_id = " . ServiceType::ID_VOIP . " 
                    AND OLD.id >= " . AccountTariff::DELTA . "
                    AND OLD.voip_number IS NOT NULL 
                    AND IFNULL(OLD.tariff_period_id, 0) != IFNULL(NEW.tariff_period_id, 0) 
                THEN
                    CALL z_sync_postgres('{$table}', OLD.id);
                END IF;
            END
        ");
    }

    public function down()
    {
        $table = AccountTariff::tableName();

        $this->execute("DROP TRIGGER IF EXISTS `sync_{$table}_voip_insert`");
        $this->execute("
            CREATE TRIGGER `sync_{$table}_voip_insert` AFTER INSERT ON `{$table}` FOR EACH ROW BEGIN
                IF  NEW.service_type_id = " . ServiceType::ID_VOIP . " 
                    AND NEW.voip_number IS NOT NULL THEN
                    CALL z_sync_postgres('{$table}', NEW.id);
                END IF;
            END
        ");

        $this->execute("DROP TRIGGER IF EXISTS `sync_{$table}_voip_update`");
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
    }
}