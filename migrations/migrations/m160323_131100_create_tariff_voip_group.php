<?php

use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffVoip;
use app\classes\uu\model\TariffVoipGroup;

class m160323_131100_create_tariff_voip_group extends \app\classes\Migration
{
    /**
     * Накатить
     */
    public function safeUp()
    {
        $this->createTariffVoipGroup();
        $this->addTariffVoipGroupId();
    }

    /**
     * Откатить
     */
    public function safeDown()
    {
        $this->dropTariffVoipGroupId();
        $this->dropTariffVoipGroup();
    }

    /**
     * Создать таблицу групп
     */
    public function createTariffVoipGroup()
    {
        $tableName = TariffVoipGroup::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            // текст
            'name' => $this->string()->notNull(),
        ]);

        $this->batchInsert($tableName, ['id', 'name'], [
            [TariffVoipGroup::ID_DEFAULT, 'Универсальные'],
            [TariffVoipGroup::ID_DEFAULT + 1, 'Местные'],
            [TariffVoipGroup::ID_DEFAULT + 2, 'Междугородние'],
            [TariffVoipGroup::ID_DEFAULT + 3, 'Международные']
        ]);
    }

    /**
     * Удалить таблицу групп
     */
    public function dropTariffVoipGroup()
    {
        $this->dropTable(TariffVoipGroup::tableName());
    }

    /**
     * Создать тариф телефонии
     */
    protected function addTariffVoipGroupId()
    {
        $tableName = Tariff::tableName();
        $fieldName = 'voip_group_id';
        $this->addColumn($tableName, $fieldName, $this->integer());
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName,
            TariffVoipGroup::tableName(), 'id', 'RESTRICT');
    }

    /**
     * Удалить тариф телефонии
     */
    protected function dropTariffVoipGroupId()
    {
        $tableName = Tariff::tableName();
        $fieldName = 'voip_group_id';
        $this->dropForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName);
        $this->dropColumn($tableName, $fieldName);
    }

}