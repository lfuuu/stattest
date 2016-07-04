<?php

use app\classes\uu\model\Resource;
use app\classes\uu\model\ServiceType;

class m160212_171200_convert_vpn_tariff extends \app\classes\Migration
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
            'id' => ServiceType::ID_VPN,
            'name' => 'VPN',
        ]);
    }

    /**
     * Удалить тип услуги
     */
    protected function deleteServiceType()
    {
        $tableName = ServiceType::tableName();
        $this->delete($tableName, [
            'id' => ServiceType::ID_VPN,
        ]);
    }

    /**
     * Создать ресурс
     */
    protected function addResource()
    {
        $tableName = Resource::tableName();

        $this->insert($tableName, [
            'id' => Resource::ID_VPN_TRAFFIC,
            'name' => 'Трафик',
            'unit' => 'Мб.',
            'min_value' => 0,
            'max_value' => null,
            'service_type_id' => ServiceType::ID_VPN,
        ]);
    }

    /**
     * Удалить ресурс
     */
    protected function deleteResource()
    {
        $tableName = Resource::tableName();
        $this->delete($tableName, [
            'id' => Resource::ID_VPN_TRAFFIC,
        ]);
    }
}