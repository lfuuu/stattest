<?php

use app\classes\uu\model\AccountEntry;
use app\classes\uu\model\AccountLogMin;
use app\classes\uu\model\AccountLogPeriod;
use app\classes\uu\model\AccountLogResource;
use app\classes\uu\model\AccountLogSetup;

class m161206_150154_add_uu_entry_date extends \app\classes\Migration
{
    public function up()
    {
        $sqls = [
            'UPDATE ' . AccountLogSetup::tableName() . ' SET account_entry_id = NULL',
            'UPDATE ' . AccountLogPeriod::tableName() . ' SET account_entry_id = NULL',
            'UPDATE ' . AccountLogResource::tableName() . ' SET account_entry_id = NULL',
            'UPDATE ' . AccountLogMin::tableName() . ' SET account_entry_id = NULL',
            'DELETE FROM ' . AccountEntry::tableName(),
        ];
        foreach ($sqls as $sql) {
            $this->execute($sql);
        }

        $tableName = AccountEntry::tableName();
        $this->addColumn($tableName, 'tariff_period_id', $this->integer()->notNull());
        $this->addColumn($tableName, 'date_from', $this->date()->notNull());
        $this->addColumn($tableName, 'date_to', $this->date()->notNull());
    }

    public function down()
    {
        $tableName = AccountEntry::tableName();
        $this->dropColumn($tableName, 'tariff_period_id');
        $this->dropColumn($tableName, 'date_from');
        $this->dropColumn($tableName, 'date_to');
    }
}