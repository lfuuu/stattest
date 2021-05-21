<?php

/**
 * Class m210519_142324_uu_tariff_tax
 */
class m210519_142324_uu_tariff_tax extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\modules\uu\models\Tariff::tableName(), 'tax_rate', $this->integer());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\modules\uu\models\Tariff::tableName(), 'tax_rate');
    }
}
