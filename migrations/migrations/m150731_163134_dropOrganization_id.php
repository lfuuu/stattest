<?php

class m150731_163134_dropOrganization_id extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `clients`
                DROP COLUMN `organization_id`;
        ");
    }

    public function down()
    {
        echo "m150731_163134_dropOrganization_id cannot be reverted.\n";

        return false;
    }
}