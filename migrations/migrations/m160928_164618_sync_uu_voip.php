<?php

use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\ServiceType;

class m160928_164618_sync_uu_voip extends \app\classes\Migration
{
    /**
     * Передавать в низкоуровневый биллинг все изменения (в том числе смены тарифа в будущем), а не только фактически свершившиеся
     * Чтобы не было лага в полночь (сначала биллер выключит, а через несколько минут получит обновление и включит)
     */
    public function up()
    {
        $table = AccountTariff::tableName();

        $this->execute("DROP TRIGGER IF EXISTS `sync_{$table}_voip_update`");
        $this->execute("
            CREATE TRIGGER `sync_{$table}_voip_update` AFTER UPDATE ON `{$table}` FOR EACH ROW BEGIN
                IF  OLD.service_type_id = " . ServiceType::ID_VOIP . " 
                    AND OLD.id >= " . AccountTariff::DELTA . "
                    AND OLD.voip_number IS NOT NULL 
                THEN
                    CALL z_sync_postgres('{$table}', OLD.id);
                END IF;
            END
        ");
    }

    public function down()
    {
        $table = AccountTariff::tableName();

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
}