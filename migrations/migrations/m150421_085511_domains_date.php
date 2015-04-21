<?php

class m150421_085511_domains_date extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE `domains`
            MODIFY COLUMN `actual_from`  date NOT NULL DEFAULT '0000-00-00' AFTER `id`,
            MODIFY COLUMN `actual_to`  date NOT NULL DEFAULT '0000-00-00' AFTER `actual_from`;
        ");

    }

    public function down()
    {
        echo "m150421_085511_domains_date cannot be reverted.\n";

        return false;
    }
}
