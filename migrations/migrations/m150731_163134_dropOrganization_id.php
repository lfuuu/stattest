<?php

class m150731_163134_dropOrganization_id extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `clients`
                DROP COLUMN `organization_id`;

            UPDATE history_version SET data_json = REPLACE(data_json,
            SUBSTRING(history_version.`data_json`,
                LOCATE('\"organization_id\":', history_version.`data_json`),
                 18 + LOCATE(',', SUBSTRING(history_version.`data_json`, LOCATE('\"organization_id\":', history_version.`data_json`)+18,5))
              ), ''
            )
            WHERE model = 'ClientAccount' AND data_json LIKE '%\"organization_id\":%'
            ;
        ");
    }

    public function down()
    {
        echo "m150731_163134_dropOrganization_id cannot be reverted.\n";

        return false;
    }
}