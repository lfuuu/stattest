<?php
use app\classes\uu\model\Period;
use app\classes\uu\model\TariffPeriod;

/**
 * Class m170223_144636_delete_tariff_period
 */
class m170223_144636_delete_tariff_period extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $tableName = TariffPeriod::tableName();
        $this->dropForeignKey('fk-uu_tariff_period-period_id', $tableName);
        $this->dropColumn($tableName, 'period_id');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $tableName = TariffPeriod::tableName();
        $this->addColumn($tableName, 'period_id', $this->integer()->notNull()->defaultValue(Period::ID_MONTH));
        $this->addForeignKey('fk-uu_tariff_period-period_id', $tableName, 'period_id', Period::tableName(), 'id');
    }
}
