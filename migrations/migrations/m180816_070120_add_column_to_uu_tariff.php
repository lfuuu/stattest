<?php

use app\modules\uu\models\Tariff;

/**
 * Class m180816_070120_add_column_to_uu_tariff
 */
class m180816_070120_add_column_to_uu_tariff extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $tableName = Tariff::tableName();
        $this->addColumn($tableName, 'count_of_carry_period', $this->integer()->notNull()->defaultValue(0));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $tableName = Tariff::tableName();
        $this->dropColumn($tableName, 'count_of_carry_period');
    }
}
