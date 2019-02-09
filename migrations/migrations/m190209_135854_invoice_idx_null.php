<?php

/**
 * Class m190209_135854_invoice_idx_null
 */
class m190209_135854_invoice_idx_null extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(\app\models\Invoice::tableName(), 'idx', $this->integer()->defaultValue(null));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(\app\models\Invoice::tableName(), 'idx', $this->integer()->notNull()->defaultValue(1));
    }
}
