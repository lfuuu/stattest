<?php

use app\classes\Migration;
use app\models\InvoiceSettings;

/**
 * Class m231120_122010_invoice_settings_tax_reason
 */
class m231120_122010_invoice_settings_tax_reason extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(InvoiceSettings::tableName(), 'tax_reason', $this->string(1024)->notNull()->defaultValue(''));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(InvoiceSettings::tableName(), 'tax_reason');
    }
}
