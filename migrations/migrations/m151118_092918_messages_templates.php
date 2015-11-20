<?php

class m151118_092918_messages_templates extends \app\classes\Migration
{
    public function up()
    {
        $this->execute('
            CREATE TABLE `message_template` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(150) NOT NULL,
                PRIMARY KEY (`id`)
            ) COLLATE="utf8_general_ci" ENGINE=InnoDB;
        ');

        $this->execute('
            CREATE TABLE `message_template_content` (
                `template_id` INT(11) NULL,
                `lang_code` VARCHAR(5) NULL DEFAULT "ru-RU",
                `type` ENUM("email","sms") NULL DEFAULT "email",
                `title` VARCHAR(150) NULL,
                `content` MEDIUMTEXT NULL,
                UNIQUE INDEX `template_id_lang_code_type` (`template_id`, `lang_code`, `type`),
                INDEX `message_template_content__lang_code` (`lang_code`),
                CONSTRAINT `message_template_content__lang_code` FOREIGN KEY (`lang_code`) REFERENCES `language` (`code`) ON UPDATE CASCADE ON DELETE CASCADE
            )
            COLLATE="utf8_general_ci" ENGINE=InnoDB
        ');
    }

    public function down()
    {
        echo "m151118_092918_messages_templates cannot be reverted.\n";
        $this->execute('DROP TABLE `message_template`');

        return true;
    }
}