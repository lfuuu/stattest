<?php

class m151111_133118_document_folder_default extends \app\classes\Migration
{
    public function up()
    {
        $this->execute('
            ALTER TABLE `document_folder`
                ADD COLUMN `is_default` TINYINT(1) NULL DEFAULT "0";
        ');

        $this->execute('
            UPDATE `document_folder` SET `is_default` = 1 WHERE `id` = 3;
        ');
    }

    public function down()
    {
        echo "m151111_133118_document_folder_default cannot be reverted.\n";

        return false;
    }
}