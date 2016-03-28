<?php

use app\classes\uu\model\Resource;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffStatus;
use app\classes\uu\model\TariffVoipCity;
use app\classes\uu\model\TariffVoipTarificate;
use app\models\City;

class m160203_132400_create_voip_tariff extends \app\classes\Migration
{
    /**
     * Накатить
     */
    public function safeUp()
    {
        $this->addServiceType();
        $this->addResource();
        $this->addTariffStatus();
        $this->createTariffVoipTarificate();
        $this->createTariffVoip();
        $this->createConnectionPoint();
    }

    /**
     * Откатить
     */
    public function safeDown()
    {
        $this->dropConnectionPoint();
        $this->dropTariffVoip();
        $this->dropTariffVoipTarificate();
        $this->deleteTariffStatus();
        $this->deleteResource();
        $this->deleteServiceType();
    }

    /**
     * Создать тип услуги
     */
    protected function addServiceType()
    {
        $tableName = ServiceType::tableName();
        $this->insert($tableName, [
            'id' => ServiceType::ID_VOIP,
            'name' => 'Телефония',
        ]);
    }

    /**
     * Удалить тип услуги
     */
    protected function deleteServiceType()
    {
        $tableName = ServiceType::tableName();
        $this->delete($tableName, [
            'id' => ServiceType::ID_VOIP,
        ]);
    }


    /**
     * Создать статус
     */
    protected function addTariffStatus()
    {
        $tableName = TariffStatus::tableName();

        $this->insert($tableName, [
            'id' => TariffStatus::ID_VOIP_8800,
            'name' => '8-800',
            'service_type_id' => ServiceType::ID_VOIP,
        ]);

        $this->insert($tableName, [
            'id' => TariffStatus::ID_VOIP_OPERATOR,
            'name' => 'Операторский',
            'service_type_id' => ServiceType::ID_VOIP,
        ]);

        $this->insert($tableName, [
            'id' => TariffStatus::ID_VOIP_TRANSIT,
            'name' => 'Переходный',
            'service_type_id' => ServiceType::ID_VOIP,
        ]);
    }

    /**
     * Удалить статус
     */
    protected function deleteTariffStatus()
    {
        $tableName = TariffStatus::tableName();
        $this->delete($tableName, [
            'id' => [
                TariffStatus::ID_VOIP_8800,
                TariffStatus::ID_VOIP_OPERATOR,
                TariffStatus::ID_VOIP_TRANSIT,
            ],
        ]);
    }

    /**
     * Создать тариф телефонии
     */
    protected function createTariffVoip()
    {
        $tableName = Tariff::tableName();
        $fieldName = 'voip_tarificate_id';
        $this->addColumn($tableName, $fieldName, $this->integer());
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, TariffVoipTarificate::tableName(), 'id', 'RESTRICT');
    }

    /**
     * Удалить тариф телефонии
     */
    protected function dropTariffVoip()
    {
        $tableName = Tariff::tableName();
        $fieldName = 'voip_tarificate_id';
        $this->dropForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName);
        $this->dropColumn($tableName, $fieldName);
    }

    /**
     * Создать группы тарификации
     */
    protected function createTariffVoipTarificate()
    {
        $tableName = TariffVoipTarificate::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            // текст
            'name' => $this->string()->notNull(),
        ]);

        $this->insert($tableName, [
            'id' => TariffVoipTarificate::ID_VOIP_BY_SECOND,
            'name' => 'Посекундно',
        ]);

        $this->insert($tableName, [
            'id' => TariffVoipTarificate::ID_VOIP_BY_SECOND_FREE,
            'name' => 'Посекундно, 5 сек. бесплатно',
        ]);

        $this->insert($tableName, [
            'id' => TariffVoipTarificate::ID_VOIP_BY_MINUTE,
            'name' => 'Поминутно',
        ]);

        $this->insert($tableName, [
            'id' => TariffVoipTarificate::ID_VOIP_BY_MINUTE_FREE,
            'name' => 'Поминутно, 5 сек. бесплатно',
        ]);
    }

    /**
     * Удалить группы тарификации
     */
    protected function dropTariffVoipTarificate()
    {
        $this->dropTable(TariffVoipTarificate::tableName());
    }

    /**
     * Создать ресурс
     */
    protected function addResource()
    {
        $tableName = Resource::tableName();

        $this->insert($tableName, [
            'id' => Resource::ID_VOIP_LINE,
            'name' => 'Линии',
            'unit' => 'шт.',
            'min_value' => 1,
            'max_value' => null,
            'service_type_id' => ServiceType::ID_VOIP,
        ]);

        $this->insert($tableName, [
            'id' => Resource::ID_VOIP_CALLS,
            'name' => 'Звонки',
            'unit' => 'у.е.',
            'min_value' => 0,
            'max_value' => null,
            'service_type_id' => ServiceType::ID_VOIP,
        ]);
    }

    /**
     * Удалить ресурс
     */
    protected function deleteResource()
    {
        $tableName = Resource::tableName();
        $this->delete($tableName, [
            'id' => [
                Resource::ID_VOIP_LINE,
                Resource::ID_VOIP_CALLS,
            ],
        ]);
    }

    /**
     * создать TariffVoipCity
     */
    protected function createConnectionPoint()
    {
        $tableName = TariffVoipCity::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            // fk
            'tariff_id' => $this->integer()->notNull(),
            'city_id' => $this->integer()->notNull(),
        ]);

        $fieldName = 'tariff_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, Tariff::tableName(), 'id', 'CASCADE');

        $fieldName = 'city_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, City::tableName(), 'id', 'RESTRICT');
    }

    /**
     * удалить TariffVoipCity
     */
    protected function dropConnectionPoint()
    {
        $tableName = TariffVoipCity::tableName();
        $this->dropTable($tableName);
    }
}