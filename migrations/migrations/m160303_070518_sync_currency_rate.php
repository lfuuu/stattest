<?php

class m160303_070518_sync_currency_rate extends \app\classes\Migration
{
    public function up()
    {
        $this->alterColumn('z_sync_postgres', 'tname', 'enum(\'clients\',\'usage_voip\',\'usage_voip_package\',\'tarifs_voip\',\'log_tarif\',\'usage_trunk\',\'usage_trunk_settings\',\'organization\',\'prefixlist\',\'tariff_package\',\'dest_prefixes\', \'currency_rate\') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL');
        $this->executeRaw('
                    CREATE TRIGGER `to_postgres_currency_rate_after_ins_tr` AFTER INSERT ON `currency_rate` FOR EACH ROW BEGIN
                        call z_sync_postgres(\'currency_rate\', NEW.id);
                    END;
                ');
    }

    public function down()
    {
        $this->execute('DROP TRIGGER IF EXISTS to_postgres_currency_rate_after_ins_tr');
        $this->alterColumn('z_sync_postgres', 'tname', 'enum(\'clients\',\'usage_voip\',\'usage_voip_package\',\'tarifs_voip\',\'log_tarif\',\'usage_trunk\',\'usage_trunk_settings\',\'organization\',\'prefixlist\',\'tariff_package\',\'dest_prefixes\') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL');
    }
}