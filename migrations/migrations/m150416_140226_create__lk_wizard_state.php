<?php

class m150416_140226_create__lk_wizard_state extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            CREATE TABLE `lk_wizard_state` (
                `account_id` int(11) NOT NULL,
                `step` tinyint(4) NOT NULL DEFAULT '0',
                `state` enum('rejected','review','approve','process') NOT NULL DEFAULT 'process',
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
        echo "m150416_140226_create__lk_wizard_state cannot be reverted.\n";

        return false;
    }
}
