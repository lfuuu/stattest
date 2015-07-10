<?php

class m150706_135922_organization_tax_system extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
          ALTER TABLE `organization`
              ADD COLUMN `is_simple_tax_system` TINYINT(1) NOT NULL DEFAULT '0' AFTER `tax_system`;
        ");

        $this->execute("
            UPDATE `organization` SET `is_simple_tax_system` = 1 WHERE `firma` = 'mcm';
        ");

        $this->execute("
            ALTER TABLE `organization`
                DROP COLUMN `tax_system`;
        ");
    }

    public function down()
    {
        echo "m150706_135922_organization_tax_system cannot be reverted.\n";

        return false;
    }
}