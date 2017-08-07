<?php
use app\modules\uu\models\AccountTariff;

/**
 * Class m170531_141719_uu_account_tariff_transfer
 */
class m170531_141719_uu_account_tariff_transfer extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(AccountTariff::tableName(), 'prev_usage_id', $this->integer()->notNull()->defaultValue(0));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(AccountTariff::tableName(), 'prev_usage_id');
    }
}
