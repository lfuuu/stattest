<?php

class m151001_143105_reward extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            CREATE TABLE `client_contract_reward` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `contract_id` INT(10) UNSIGNED NOT NULL,
                `usage_type` INT(10) UNSIGNED NOT NULL,
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
    }

    public function down()
    {
        echo "m151001_143105_reward cannot be reverted.\n";

        return false;
    }
}