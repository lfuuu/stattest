<?php

class m160321_161935_message_content_template extends \app\classes\Migration
{
    public function up()
    {
        $this->addColumn('message_template_content', 'filename', $this->string(255));
        $this->alterColumn('message_template_content', 'type',
            'ENUM("email","sms","email_inner") NULL DEFAULT "email"');
    }

    public function down()
    {
        $this->dropColumn('message_template_content', 'filename');
        $this->alterColumn('message_template_content', 'type', 'ENUM("email","sms") NULL DEFAULT "email"');
    }
}