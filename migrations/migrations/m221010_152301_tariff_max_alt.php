<?php

/**
 * Class m221010_152301_tariff_max_alt
 */
class m221010_152301_tariff_max_alt extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\modules\uu\models\Tariff::tableName(), 'is_one_alt', $this->integer()->notNull()->defaultValue(0));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\modules\uu\models\Tariff::tableName(), 'is_one_alt');
    }
}
