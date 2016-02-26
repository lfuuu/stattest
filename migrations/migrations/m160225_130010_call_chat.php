<?php

class m160225_130010_call_chat extends \app\classes\Migration
{
    public function up()
    {
        $this->createTable("tarifs_call_chat", [
            "id" => "int(11) NOT NULL",
            "description" => "varchar(100) NOT NULL DEFAULT ''",
            "price" => "decimal(13,4) NOT NULL DEFAULT '0.0000'",
            "currency_id" => "char(3) NOT NULL DEFAULT 'USD'",
            "price_include_vat" => "tinyint(1) DEFAULT '1'",
            "status" => "enum('public','archive') NOT NULL DEFAULT 'public'",
            "edit_user" => "int(11) NOT NULL DEFAULT '0'",
            "edit_time" => "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'"

        ], 'ENGINE=InnoDB CHARSET=utf8');

        $this->addPrimaryKey("id", "tarifs_call_chat", "id");

        $this->execute("alter table tarifs_call_chat modify id int(11) NOT NULL AUTO_INCREMENT");


        $this->createTable("usage_call_chat", [
            "id" => "int(11) NOT NULL",
            "client" => "varchar(100) NOT NULL DEFAULT ''",
            "activation_dt" => "datetime DEFAULT NULL",
            "expire_dt" => "datetime DEFAULT NULL",
            "actual_from" => "date NOT NULL DEFAULT '4000-01-01'",
            "actual_to" => "date NOT NULL DEFAULT '4000-01-01'",
            "status" => "enum('connecting','working') NOT NULL DEFAULT 'working'",
            "comment" => "varchar(255) NOT NULL DEFAULT ''",
            "tarif_id" => "int(11) NOT NULL DEFAULT '0'",
            "prev_usage_id" => "int(11) DEFAULT '0'",
            "next_usage_id" => "int(11) DEFAULT '0'"
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addPrimaryKey("id", "usage_call_chat", "id");

        $this->execute("alter table usage_call_chat modify id int(11) NOT NULL AUTO_INCREMENT");
        $this->createIndex("client", "usage_call_chat", "client");

    }

    public function down()
    {
        $this->dropTable("tarifs_call_chat");
        $this->dropTable("usage_call_chat");
    }
}