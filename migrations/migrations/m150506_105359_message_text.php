<?php

class m150506_105359_message_text extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            CREATE TABLE `message_text` (
                        `message_id` INT(11) NOT NULL,
                        `text` TEXT NOT NULL,
                        PRIMARY KEY (`message_id`)
                )
                COLLATE='utf8_general_ci'
                ENGINE=InnoDB
");
    }

    public function down()
    {
        echo "m150506_105359_message_text cannot be reverted.\n";

        return false;
    }
}