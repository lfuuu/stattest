<?php

use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffHeap;

/**
 * Class m180911_085913_alter_relation_on_uu_account_tariff_heap
 */
class m180911_085913_alter_relation_on_uu_account_tariff_heap extends \app\classes\Migration
{
    private $_foreignKey = 'account_tariff_id_fk';
    private $_index = 'account_tariff_id_idx';
    private $_column = 'account_tariff_id';

    /**
     * Up
     */
    public function safeUp()
    {
        $tableName = AccountTariffHeap::tableName();
        $this->dropForeignKey($this->_foreignKey, $tableName);
        $this->dropIndex($this->_index, $tableName);

        $this->createIndex($this->_index, $tableName, $this->_column);
        $this->addForeignKey($this->_foreignKey, $tableName, $this->_column, AccountTariff::tableName(), 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $tableName = AccountTariffHeap::tableName();
        $this->dropForeignKey($this->_foreignKey, $tableName);
        $this->dropIndex($this->_index, $tableName);

        $this->createIndex($this->_index, $tableName, $this->_column);
        $this->addForeignKey($this->_foreignKey, $tableName, $this->_column, AccountTariff::tableName(), 'id');
    }
}
