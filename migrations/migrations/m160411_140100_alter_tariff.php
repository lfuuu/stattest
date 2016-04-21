<?php

use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\Tariff;

class m160411_140100_alter_tariff extends \app\classes\Migration
{
    public function safeUp()
    {
        $tableName = Tariff::tableName();
        $this->alterColumn($tableName, 'insert_time', $this->dateTime());
        $this->alterColumn($tableName, 'update_time', $this->timestamp());

        $tableName = AccountTariff::tableName();
        $this->alterColumn($tableName, 'insert_time', $this->dateTime());
        $this->alterColumn($tableName, 'update_time', $this->timestamp());
    }

    public function safeDown()
    {
        $tableName = Tariff::tableName();
        $this->alterColumn($tableName, 'insert_time', $this->timestamp());
        $this->alterColumn($tableName, 'update_time', $this->dateTime());

        $tableName = AccountTariff::tableName();
        $this->alterColumn($tableName, 'insert_time', $this->timestamp());
        $this->alterColumn($tableName, 'update_time', $this->dateTime());
    }
}