<?php

/**
 * Class m240404_115509_add_is_portrait_to_payment_template_type
 */
class m240404_115509_add_is_portrait_to_payment_template_type extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\models\document\PaymentTemplateType::tableName(), 'is_portrait', $this->smallInteger()->notNull()->defaultValue(1));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\models\document\PaymentTemplateType::tableName(), 'is_portrait');
    }
}
