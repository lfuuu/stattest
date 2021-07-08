<?php

use app\modules\uu\models\Tariff;
/**
 * Class m210708_132551_uu_tariff_overview
 */
class m210708_132551_uu_tariff_overview extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Tariff::tableName(), 'overview', $this->text());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Tariff::tableName(), 'overview');
    }
}
