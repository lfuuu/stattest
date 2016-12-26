<?php
use app\models\ClientAccount;

/**
 * Class m161226_102432_account_tax_value
 */
class m161226_102432_account_tax_value extends \app\classes\Migration
{
    private $_field = 'is_calc_with_tax';

    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(ClientAccount::tableName(), $this->_field, $this->integer()->defaultValue(null));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(ClientAccount::tableName(), $this->_field);
    }
}
