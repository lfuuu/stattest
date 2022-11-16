<?php

/**
 * Class m221116_140814_payment_type
 */
class m221116_140814_payment_type extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\models\Payment::tableName(), 'payment_type', $this->integer()->notNull()->defaultValue(1));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\models\Payment::tableName(), 'payment_type');
    }
}
