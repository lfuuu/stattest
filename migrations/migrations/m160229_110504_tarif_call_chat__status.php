<?php

class m160229_110504_tarif_call_chat__status extends \app\classes\Migration
{
    public function up()
    {
        $this->alterColumn('tarifs_call_chat', 'status', 'enum(\'public\',\'special\',\'archive\') NOT NULL DEFAULT \'public\'');
    }

    public function down()
    {
        $this->alterColumn('tarifs_call_chat', 'status', 'enum(\'public\',\'archive\') NOT NULL DEFAULT \'public\'');
    }
}