<?php

class m160225_130010_call_chat extends \app\classes\Migration
{
    public function up()
    {
        $this->createTable("tarifs_call_chat", [
            "id" => "int(11) NOT NULL",
            "description" => "varchar(100) NOT NULL DEFAULT ''",
            "price" => "decimal(13,4) NOT NULL DEFAULT '0.0000'",
            "currency_id" => "char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'USD'",
            "price_include_vat" => "tinyint(1) DEFAULT '1'",
            "status" => "enum('public','archive') NOT NULL DEFAULT 'public'",
            "edit_user" => "int(11) NOT NULL DEFAULT '0'",
            "edit_time" => "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'"

        ], 'ENGINE=InnoDB CHARSET=utf8');

        $this->addPrimaryKey("id", "tarifs_call_chat", "id");

        $this->execute("alter table tarifs_call_chat modify id int(11) NOT NULL AUTO_INCREMENT");

    }

    public function down()
    {
        $this->dropTable("tarifs_call_chat");
    }
}
