<?php

use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffHeap;


/**
 * Class m180802_083803_uu_account_tariff_heap
 */
class m180802_083803_uu_account_tariff_heap extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        // Удаление колонок из таблицы uu_account_tariff
        $accountTariffTableName = AccountTariff::tableName();
        $this->dropColumn($accountTariffTableName, 'test_connect_date');
        $this->dropColumn($accountTariffTableName, 'disconnect_date');
        // Создание таблицы uu_account_tariff_heap
        $tableName = AccountTariffHeap::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            'account_tariff_id' => $this->integer()->notNull(),
            'test_connect_date' => $this->dateTime()->comment('Дата включения на тестовый тариф'),
            'date_sale' => $this->dateTime()->comment('Дата продажи'),
            'date_before_sale' => $this->dateTime()->comment('Дата допродажи'),
            'disconnect_date' => $this->dateTime()->comment('Дата отключения'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->createIndex('account_tariff_id_idx', $tableName, 'account_tariff_id', true);
        $this->addForeignKey('account_tariff_id_fk', $tableName, 'account_tariff_id', AccountTariff::tableName(), 'id');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        // Добавление колонок в таблицу uu_account_tariff
        $accountTariffTableName = AccountTariff::tableName();
        $this->addColumn($accountTariffTableName, 'test_connect_date', $this->dateTime());
        $this->addColumn($accountTariffTableName, 'disconnect_date', $this->dateTime());
        // Удаление таблицы uu_account_tariff_heap
        $this->dropTable(AccountTariffHeap::tableName());
    }
}
