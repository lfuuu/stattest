<?php

class m151001_143105_reward extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
                CREATE TABLE `client_contract_reward` (
                    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `contract_id` INT(10) UNSIGNED NOT NULL,
                    `usage_type` ENUM('voip','virtpbx') NOT NULL,
                    `once_only` SMALLINT(5) UNSIGNED NOT NULL,
                    `percentage_of_fee` SMALLINT(5) UNSIGNED NOT NULL,
                    `percentage_of_over` SMALLINT(5) UNSIGNED NOT NULL,
                    `period_type` ENUM('always','month') NOT NULL DEFAULT 'always',
                    `period_month` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE INDEX `contract_id_usage_type` (`contract_id`, `usage_type`)
                )
                ENGINE=InnoDB
                ;
        ");

        $this->execute("
            ALTER TABLE `client_contract`
            	ADD COLUMN `lk_access` ENUM('full','readonly','noaccess') NULL DEFAULT NULL AFTER `is_external`;
        ");

        $this->execute("
            UPDATE `client_contract_business_process_status` SET `sort`=1 WHERE  `id`=35;
            INSERT INTO `client_contract_business_process_status` (`id`, `business_process_id`, `name`) VALUES (125, 8, 'Переговоры');
            INSERT INTO `client_contract_business_process_status` (`id`, `business_process_id`, `name`, `sort`) VALUES (126, 8, 'Ручной счет', 2);
            INSERT INTO `client_contract_business_process_status` (`id`, `business_process_id`, `name`, `sort`) VALUES (127, 8, 'Приостановлен', 3);
            INSERT INTO `client_contract_business_process_status` (`id`, `business_process_id`, `name`, `sort`) VALUES (128, 8, 'Расторгнут', 4);
            INSERT INTO `client_contract_business_process_status` (`id`, `business_process_id`, `name`, `sort`) VALUES (129, 8, 'Отказ', 5);
            INSERT INTO `client_contract_business_process_status` (`id`, `business_process_id`, `name`, `sort`) VALUES (130, 8, 'Мусор', 6);
        ");
    }

    public function down()
    {
        echo "m151001_143105_reward cannot be reverted.\n";

        return false;
    }
}