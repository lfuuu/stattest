<?php

/**
 * Class m190108_125601_voip_number_source
 */
class m190108_125601_voip_number_source extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(
            \app\models\Number::tableName(),
            'source',
            "enum('portability','operator','regulator','portability_not_for_sale','operator_not_for_sale') DEFAULT 'operator'"
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\models\Number::tableName(), 'source');
    }
}
