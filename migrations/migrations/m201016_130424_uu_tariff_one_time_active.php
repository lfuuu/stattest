<?php

use app\classes\Migration;
use app\modules\uu\models\Tariff;

/**
 * Class m201016_130424_uu_tariff_one_time_active
 */
class m201016_130424_uu_tariff_one_time_active extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Tariff::tableName(), 'is_one_active', $this->tinyInteger()->notNull()->defaultValue(0));
        $this->addColumn(Tariff::tableName(), 'is_proportionately', $this->tinyInteger()->notNull()->defaultValue(1));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Tariff::tableName(), 'is_one_active');
        $this->dropColumn(Tariff::tableName(), 'is_proportionately');
    }
}
