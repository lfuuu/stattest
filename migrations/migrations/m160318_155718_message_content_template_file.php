<?php

class m160318_155718_message_content_template_file extends \app\classes\Migration
{
    public function up()
    {
        $this->addColumn('message_template_content', 'file', 'VARCHAR(255) NULL AFTER `content`');
    }

    public function down()
    {
        $this->dropColumn('message_template_content', 'file');
    }
}