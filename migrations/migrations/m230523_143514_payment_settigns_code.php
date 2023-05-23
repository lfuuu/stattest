<?php

use app\classes\Migration;
use app\models\InvoiceSettings;

/**
 * Class m230523_143514_payment_settigns_code
 */
class m230523_143514_payment_settigns_code extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(InvoiceSettings::tableName(), 'at_account_code', $this->string(32)->notNull()->defaultValue(''));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(InvoiceSettings::tableName(), 'at_account_code');
    }
}
