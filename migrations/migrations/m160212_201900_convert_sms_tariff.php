<?php

use app\classes\uu\model\Period;
use app\classes\uu\model\Resource;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPeriod;
use app\classes\uu\model\TariffPerson;
use app\classes\uu\model\TariffResource;
use app\classes\uu\model\TariffStatus;
use app\models\Country;

class m160212_201900_convert_sms_tariff extends \app\classes\Migration
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
            'id' => ServiceType::ID_SMS,
            'name' => 'SMS',
        ]);
    }

    /**
     * Удалить тип услуги
     */
    protected function deleteServiceType()
    {
        $tableName = ServiceType::tableName();
        $this->delete($tableName, [
            'id' => ServiceType::ID_SMS,
        ]);
    }

    /**
     * Создать ресурс
     */
    protected function addResource()
    {
        $tableName = Resource::tableName();

        $this->insert($tableName, [
            'id' => Resource::ID_SMS,
            'name' => 'СМС',
            'unit' => 'шт.',
            'min_value' => 0,
            'max_value' => null,
            'service_type_id' => ServiceType::ID_SMS,
        ]);
    }

    /**
     * Удалить ресурс
     */
    protected function deleteResource()
    {
        $tableName = Resource::tableName();
        $this->delete($tableName, [
            'id' => Resource::ID_SMS,
        ]);
    }
}