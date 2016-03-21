<?php

class m160318_084753_message_content_template_type extends \app\classes\Migration
{
    public function up()
    {
        $this->execute('
            ALTER TABLE `message_template_content`
                CHANGE COLUMN `type` `type` ENUM("email","sms","email_inner") NULL DEFAULT "email";
        ');
    }

    public function down()
    {
        $this->execute('
            ALTER TABLE `message_template_content`
                CHANGE COLUMN `type` `type` ENUM("email","sms") NULL DEFAULT "email";
        ');
    }
}