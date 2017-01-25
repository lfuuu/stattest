<?php

use app\models\ContractType;

class m170124_154221_create_trigger_client_contract_type extends \app\classes\Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $table = ContractType::tableName();

        $this->execute("DROP TRIGGER IF EXISTS `sync_{$table}_after_insert`");
        $this->execute("
            CREATE TRIGGER `sync_{$table}_after_insert` AFTER INSERT ON `{$table}` FOR EACH ROW BEGIN
                call z_sync_postgres('{$table}', NEW.id);
            END
        ");

        $this->execute("DROP TRIGGER IF EXISTS `sync_{$table}_after_update`");
        $this->execute("
            CREATE TRIGGER `sync_{$table}_after_update` AFTER UPDATE ON `{$table}` FOR EACH ROW BEGIN
                call z_sync_postgres('{$table}', NEW.id);
            END
        ");
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $table = ContractType::tableName();

        $this->execute("DROP TRIGGER IF EXISTS `sync_{$table}_after_insert`");
        $this->execute("DROP TRIGGER IF EXISTS `sync_{$table}_after_update`");
    }
}
