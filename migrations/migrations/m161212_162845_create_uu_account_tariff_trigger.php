<?php
use app\classes\uu\model\AccountTariff;

/**
 * Об удалении (отмене) услуги тоже надо сообщать в низкоуровневый биллер
 */
class m161212_162845_create_uu_account_tariff_trigger extends \app\classes\Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $accountTariffTableName = AccountTariff::tableName();

        $this->execute("DROP TRIGGER IF EXISTS `sync_{$accountTariffTableName}_voip_update`");
        $this->execute("
            CREATE TRIGGER `sync_{$accountTariffTableName}_voip_update` AFTER UPDATE ON `{$accountTariffTableName}` FOR EACH ROW BEGIN
                IF  OLD.service_type_id = 2 
                    AND OLD.id >= 100000
                THEN
                    CALL z_sync_postgres('{$accountTariffTableName}', OLD.id);
                END IF;
            END
        ");

        $this->execute("
            CREATE TRIGGER `sync_{$accountTariffTableName}_voip_delete` AFTER DELETE ON `{$accountTariffTableName}` FOR EACH ROW BEGIN
                IF  OLD.service_type_id = 2 
                    AND OLD.id >= 100000
                THEN
                    CALL z_sync_postgres('{$accountTariffTableName}', OLD.id);
                END IF;
            END
        ");
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $accountTariffTableName = AccountTariff::tableName();

        $this->execute("DROP TRIGGER IF EXISTS `sync_{$accountTariffTableName}_voip_delete`");

        $this->execute("DROP TRIGGER IF EXISTS `sync_{$accountTariffTableName}_voip_update`");
        $this->execute("
            CREATE TRIGGER `sync_{$accountTariffTableName}_voip_update` AFTER UPDATE ON `{$accountTariffTableName}` FOR EACH ROW BEGIN
                IF  OLD.service_type_id = 2 
                    AND OLD.id >= 100000
                    AND OLD.voip_number IS NOT NULL 
                THEN
                    CALL z_sync_postgres('{$accountTariffTableName}', OLD.id);
                END IF;
            END
        ");
    }
}
