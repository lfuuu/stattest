<?php

use app\classes\uu\model\Resource;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\TariffStatus;

class m160212_134200_convert_internet_tariff extends \app\classes\Migration
{
    /**
     * Накатить
     */
    public function safeUp()
    {
        $this->addServiceType();
        $this->addTariffStatus();
        $this->addResource();
    }

    /**
     * Откатить
     */
    public function safeDown()
    {
        $this->deleteResource();
        $this->deleteTariffStatus();
        $this->deleteServiceType();
    }

    /**
     * Создать тип услуги
     */
    protected function addServiceType()
    {
        $tableName = ServiceType::tableName();
        $this->insert($tableName, [
            'id' => ServiceType::ID_INTERNET,
            'name' => 'Интернет',
        ]);
    }

    /**
     * Удалить тип услуги
     */
    protected function deleteServiceType()
    {
        $tableName = ServiceType::tableName();
        $this->delete($tableName, [
            'id' => ServiceType::ID_INTERNET,
        ]);
    }

    /**
     * Создать ресурс
     */
    protected function addResource()
    {
        $tableName = Resource::tableName();

        $this->insert($tableName, [
            'id' => Resource::ID_INTERNET_TRAFFIC,
            'name' => 'Трафик',
            'unit' => 'Мб.',
            'min_value' => 0,
            'max_value' => null,
            'service_type_id' => ServiceType::ID_INTERNET,
        ]);
    }

    /**
     * Удалить ресурс
     */
    protected function deleteResource()
    {
        $tableName = Resource::tableName();
        $this->delete($tableName, [
            'id' => Resource::ID_INTERNET_TRAFFIC,
        ]);
    }

    /**
     * Создать статус
     */
    protected function addTariffStatus()
    {
        $tableName = TariffStatus::tableName();

        $this->insert($tableName, [
            'id' => TariffStatus::ID_INTERNET_ADSL,
            'name' => 'ADSL',
            'service_type_id' => ServiceType::ID_INTERNET,
        ]);
    }

    /**
     * Удалить статус
     */
    protected function deleteTariffStatus()
    {
        $tableName = TariffStatus::tableName();
        $this->delete($tableName, [
            'id' => TariffStatus::ID_INTERNET_ADSL,
        ]);
    }
}