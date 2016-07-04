<?php

use app\classes\uu\model\Resource;
use app\classes\uu\model\ServiceType;

class m160212_160100_convert_collocation_tariff extends \app\classes\Migration
{
    /**
     * Накатить
     */
    public function safeUp()
    {
        $this->addServiceType();
        $this->addResource();
    }

    /**
     * Откатить
     */
    public function safeDown()
    {
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
            'id' => ServiceType::ID_COLLOCATION,
            'name' => 'Collocation',
        ]);
    }

    /**
     * Удалить тип услуги
     */
    protected function deleteServiceType()
    {
        $tableName = ServiceType::tableName();
        $this->delete($tableName, [
            'id' => ServiceType::ID_COLLOCATION,
        ]);
    }

    /**
     * Создать ресурс
     */
    protected function addResource()
    {
        $tableName = Resource::tableName();

        $this->insert($tableName, [
            'id' => Resource::ID_COLLOCATION_TRAFFIC_RUSSIA,
            'name' => 'Трафик Russia',
            'unit' => 'Мб.',
            'min_value' => 0,
            'max_value' => null,
            'service_type_id' => ServiceType::ID_COLLOCATION,
        ]);

        $this->insert($tableName, [
            'id' => Resource::ID_COLLOCATION_TRAFFIC_RUSSIA2,
            'name' => 'Трафик Russia2',
            'unit' => 'Мб.',
            'min_value' => 0,
            'max_value' => null,
            'service_type_id' => ServiceType::ID_COLLOCATION,
        ]);

        $this->insert($tableName, [
            'id' => Resource::ID_COLLOCATION_TRAFFIC_FOREINGN,
            'name' => 'Трафик Foreign',
            'unit' => 'Мб.',
            'min_value' => 0,
            'max_value' => null,
            'service_type_id' => ServiceType::ID_COLLOCATION,
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
                Resource::ID_COLLOCATION_TRAFFIC_RUSSIA,
                Resource::ID_COLLOCATION_TRAFFIC_RUSSIA2,
                Resource::ID_COLLOCATION_TRAFFIC_FOREINGN,
            ]
        ]);
    }
}