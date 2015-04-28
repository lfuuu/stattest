<?php

class m150507_175657_create_lk_wizard extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            CREATE TABLE `lk_wizard_state` (
                `account_id` int(11) NOT NULL,
                `step` tinyint(4) NOT NULL DEFAULT '0',
                `state` enum('rejected','review','approve','process') NOT NULL DEFAULT 'process',
                `trouble_id` int(11) NOT NULL DEFAULT 0,
                PRIMARY KEY (`account_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $this->execute("RENAME table `client_person` to `client_contragent_person`");

        $this->execute("ALTER TABLE `client_contragent_person`
            CHANGE COLUMN `contraget_id` `contragent_id`  int(11) NOT NULL AFTER `id`,
            ADD COLUMN `address`  varchar(255) NOT NULL AFTER `passport_issued`;
        ");

    }

    public function down()
    {
        echo "m150507_175657_create_lk_wizard cannot be reverted.\n";

        return false;
    }
}
