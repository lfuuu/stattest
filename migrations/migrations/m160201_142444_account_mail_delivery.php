<?php

class m160201_142444_account_mail_delivery extends \app\classes\Migration
{
    public function up()
    {
        $this->createTable('client_account_options', [
            'client_account_id' => 'integer',
            'option' => 'string(150)',
            'value' => 'string',
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $this->addPrimaryKey('option', 'client_account_options', ['client_account_id', 'option', 'value']);
        $this->addForeignKey(
            'client_account_options__account_id',
            'client_account_options',
            ['client_account_id'],
            'clients',
            ['id'],
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('client_account_options');
    }
}