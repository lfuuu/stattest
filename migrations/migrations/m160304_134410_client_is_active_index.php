<?php

class m160304_134410_client_is_active_index extends \app\classes\Migration
{
    public function up()
    {
        $this->createIndex('clients__is_active', 'clients', 'is_active');
    }

    public function down()
    {
        $this->dropIndex('clients__is_active', 'clients');
    }
}