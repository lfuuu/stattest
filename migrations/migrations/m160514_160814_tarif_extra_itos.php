<?php

use app\models\TariffExtra;

class m160514_160814_tarif_extra_itos extends \app\classes\Migration
{
    public function up()
    {
        $this->alterColumn(TariffExtra::tableName(), 'status',  "enum('public','special','archive','itpark','itos') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'public'");
    }

    public function down()
    {
        $this->alterColumn(TariffExtra::tableName(), 'status',  "enum('public','special','archive','itpark') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'public'");
    }
}