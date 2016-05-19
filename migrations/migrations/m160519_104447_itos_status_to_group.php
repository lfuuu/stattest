<?php

use app\models\TariffExtra;

class m160519_104447_itos_status_to_group extends \app\classes\Migration
{
    public function up()
    {
        $this->update(TariffExtra::tableName(), ['code' => 'itos', 'status' => 'public'], ['status' => 'itos']);
        $this->alterColumn(TariffExtra::tableName(), 'status', "enum('public','special','archive','itpark') NOT NULL DEFAULT 'public'");
    }

    public function down()
    {
        $this->alterColumn(TariffExtra::tableName(), 'status', "enum('public','special','archive','itpark','itos') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'public'");
        $this->update(TariffExtra::tableName(), ['status' => 'itos', 'code' => ''], ['code' => 'itos']);
    }
}