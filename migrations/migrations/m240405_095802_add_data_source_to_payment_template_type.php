<?php

/**
 * Class m240405_095802_add_data_source_to_payment_template_type
 */
class m240405_095802_add_data_source_to_payment_template_type extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\models\document\PaymentTemplateType::tableName(), 'data_source', $this->string(255)->notNull());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\models\document\PaymentTemplateType::tableName(), 'data_source');
    }
}
