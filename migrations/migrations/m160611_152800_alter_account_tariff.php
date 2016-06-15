<?php

use app\classes\uu\model\AccountTariff;

class m160611_152800_alter_account_tariff extends \app\classes\Migration
{
    public function safeUp()
    {
        $this->alter('CASCADE');
    }

    public function safeDown()
    {
        $this->alter('RESTRICT');
    }

    /**
     */
    private function alter($type)
    {
        $tableName = AccountTariff::tableName();
        $fieldName = 'prev_account_tariff_id';
        $this->dropForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName);
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, $tableName, 'id', $type);
    }
}
