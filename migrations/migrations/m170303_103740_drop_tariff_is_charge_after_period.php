<?php

use app\classes\uu\model\Tariff;

/**
 * Handles the dropping for table `tariff_is_charge_after_period`.
 */
class m170303_103740_drop_tariff_is_charge_after_period extends \app\classes\Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->dropColumn(Tariff::tableName(), 'is_charge_after_period');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->addColumn(Tariff::tableName(), 'is_charge_after_period', $this->integer()->notNull()->defaultValue(0));
    }
}
