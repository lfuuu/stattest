<?php

use app\classes\uu\model\AccountTariff;

/**
 * Если после создания услуги изменили технический номер, то его надо заново передать в биллер
 */
class m161004_090952_create_voip_number_trigger extends \app\classes\Migration
{
    public function up()
    {
        $numberTableName = \app\models\Number::tableName();
        $usageVoipTableName = \app\models\UsageVoip::tableName();
        $accountTariffTableName = AccountTariff::tableName();

        $this->execute("DROP TRIGGER IF EXISTS `sync_{$numberTableName}_update`");

        $this->execute("
            CREATE TRIGGER `sync_{$numberTableName}_update` AFTER UPDATE ON `{$numberTableName}` FOR EACH ROW BEGIN
            
                IF  COALESCE(OLD.number_tech, '') <>  COALESCE(NEW.number_tech, '')
                    AND NEW.usage_id IS NOT NULL 
                THEN
                    CALL z_sync_postgres('{$usageVoipTableName}', NEW.usage_id);
                END IF;
                
                IF  COALESCE(OLD.number_tech, '') <>  COALESCE(NEW.number_tech, '')
                    AND NEW.uu_account_tariff_id IS NOT NULL 
                THEN
                    CALL z_sync_postgres('{$accountTariffTableName}', NEW.uu_account_tariff_id);
                END IF;
                
            END
        ");
    }

    public function down()
    {
        $numberTableName = \app\models\Number::tableName();
        $this->execute("DROP TRIGGER IF EXISTS `sync_{$numberTableName}_update`");
    }
}
