<?php

/**
 * Class m240425_121301_add_short_name_to_payment_template_type
 */
class m240425_121301_add_short_name_to_payment_template_type extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\models\document\PaymentTemplateType::tableName(), 'short_name', $this->string(255)->notNull());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\models\document\PaymentTemplateType::tableName(), 'short_name');
    }
}
