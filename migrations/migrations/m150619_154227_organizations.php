<?php

class m150619_154227_organizations extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            RENAME TABLE `organization` TO `g_organization`
        ");

        $this->execute("
            DROP TABLE IF EXISTS `organizations`
        ");

        $this->execute("
          ALTER TABLE `clients`
              ADD COLUMN `organization_id` INT (11) NULL DEFAULT '0' AFTER `firma`;
        ");

        $this->executeSqlFile('person.sql');
        $this->executeSqlFile('organization.sql');
        $this->executeSqlFile('organization_to_clients.sql');
    }

    public function down()
    {
        echo "m150619_154227_organizations cannot be reverted.\n";

        return false;
    }
}