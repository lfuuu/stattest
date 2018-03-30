<?php

use app\modules\uu\models\AccountTariff;

/**
 * Class m180329_111206_add_column_to_uu_account_tariff
 */
class m180329_111206_add_column_to_uu_account_tariff extends \app\classes\Migration
{
    private $_column = 'calltracking_params';

    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(AccountTariff::tableName(), $this->_column, $this->text());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(AccountTariff::tableName(), $this->_column);
    }
}