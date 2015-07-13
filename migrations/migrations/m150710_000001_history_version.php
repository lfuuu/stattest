<?php

class m150710_000001_history_version extends \app\classes\Migration
{

    public function up()
    {
        $this->execute("
            CREATE TABLE `history_version` (
                `model` VARCHAR(50) NOT NULL,
                `model_id` INT(11) NOT NULL,
                `date` DATE NOT NULL,
                `data_json` TEXT NOT NULL,
                PRIMARY KEY (`model`, `model_id`, `date`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=InnoDB
        ");
    }

    public function down()
    {
        echo "m150519_095927_history_version cannot be reverted.\n";

        return false;
    }

}
