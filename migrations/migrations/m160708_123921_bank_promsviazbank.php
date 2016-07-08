<?php

class m160708_123921_bank_promsviazbank extends \app\classes\Migration
{
    public function up()
    {
        $this->alterColumn(app\models\Payment::tableName(), "bank", "enum('citi','mos','ural','sber','raiffeisen','promsviazbank') NOT NULL DEFAULT 'mos'");
    }

    public function down()
    {
        $this->alterColumn(app\models\Payment::tableName(), "bank", "enum('citi','mos','ural','sber','raiffeisen') NOT NULL DEFAULT 'mos'");
    }
}