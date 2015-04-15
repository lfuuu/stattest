<?php

class m150415_193419_contragent extends \app\classes\Migration
{
    public function up()
    {

        $this->execute("
            CREATE TABLE `client_person` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `contraget_id` int(11) NOT NULL,
                `last_name` varchar(64) NOT NULL,
                `first_name` varchar(64) NOT NULL,
                `middle_name` varchar(64) NOT NULL,
                `date_of_bird` date NOT NULL,
                `passport_serial` varchar(6) NOT NULL,
                `passport_number` varchar(10) NOT NULL,
                `passport_issued` varchar(255) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            
        ");

        $this->execute("
            alter TABLE `client_contragent`
                add `legal_type` enum('person','ip','legal') NOT NULL DEFAULT 'legal',
                add `name_full` varchar(255) NOT NULL,
                add `address` varchar(255) NOT NULL DEFAULT '',
                add `inn` varchar(16) NOT NULL DEFAULT '',
                add `inn_eu` varchar(16) NOT NULL DEFAULT '',
                add `kpp` varchar(16) NOT NULL DEFAULT '',
                add `position` varchar(128) NOT NULL DEFAULT '',
                add `fio` varchar(128) NOT NULL DEFAULT '',
                add `tax_regime` enum('15','6','full') NOT NULL DEFAULT 'full',
                add `opf` varchar(255) NOT NULL DEFAULT '',
                add `okpo` varchar(255) NOT NULL DEFAULT '',
                add `okvd` varchar(255) NOT NULL DEFAULT ''
            
            ");

    }

    public function down()
    {
        echo "m150415_193419_contragent cannot be reverted.\n";

        return false;
    }
}
