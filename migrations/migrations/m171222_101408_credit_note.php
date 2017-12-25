<?php

use app\models\Payment;

/**
 * Class m171222_101408_credit_note
 */
class m171222_101408_credit_note extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(Payment::tableName(), 'type', "enum('bank','prov','ecash','neprov', '" . Payment::TYPE_CREDITNOTE . "') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'bank'");
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(Payment::tableName(), 'type', "enum('bank','prov','ecash','neprov') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'bank'");
    }
}
