<?php

class m160323_112113_client_contacts_types extends \app\classes\Migration
{
    public function up()
    {
        $this->alterColumn('client_contacts', 'type', 'ENUM("email","phone","fax","sms","email_invoice","email_rate","email_support") NOT NULL');
    }

    public function down()
    {
        $this->alterColumn('client_contacts', 'type', 'ENUM("email","phone","fax","sms") NOT NULL');
    }
}