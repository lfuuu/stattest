<?php

use app\classes\Migration;
use app\models\document\PaymentTemplateType;
use app\modules\uu\models\Tariff;

/**
 * Class m240807_133356_tariff_template
 */
class m240807_133356_tariff_template extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Tariff::tableName(), 'payment_template_type_id', $this->integer());
        $this->addForeignKey('fk-' . Tariff::tableName() . '-payment_template_type_id', Tariff::tableName(), 'payment_template_type_id', PaymentTemplateType::tableName(), 'id', 'RESTRICT', 'RESTRICT');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Tariff::tableName(), 'payment_template_type_id');
    }
}
