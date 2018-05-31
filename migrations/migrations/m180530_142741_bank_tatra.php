<?php

use app\models\Payment;

/**
 * Class m180530_142741_bank_tatra
 */
class m180530_142741_bank_tatra extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(Payment::tableName(), 'bank', $this->string(64)->notNull()->defaultValue(Payment::BANK_MOS));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(Payment::tableName(), 'bank', "enum('citi','mos','ural','sber','raiffeisen','promsviazbank','tatra') NOT NULL DEFAULT '" . Payment::BANK_MOS . "'");
    }
}
