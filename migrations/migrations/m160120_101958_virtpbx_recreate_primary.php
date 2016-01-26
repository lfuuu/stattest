<?php

class m160120_101958_virtpbx_recreate_primary extends \app\classes\Migration
{
    public function up()
    {
        $this->dropPrimaryKey('PRIMARY', 'virtpbx_stat');
        $this->addPrimaryKey('PRIMARY_UPDATE', 'virtpbx_stat', ['client_id', 'usage_id', 'date']);
    }

    public function down()
    {
        $this->dropPrimaryKey('PRIMARY', 'virtpbx_stat');
        $this->addPrimaryKey('PRIMARY_UPDATE', 'virtpbx_stat', ['client_id', 'date']);
    }
}