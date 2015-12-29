<?php

use app\classes\uu\model\ServiceType;

class m160211_135800_create_voip_package_tariff extends \app\classes\Migration
{
    /**
     * Накатить
     */
    public function safeUp()
    {
        $this->addServiceTypeParent();
        $this->addServiceType();
    }

    /**
     * Откатить
     */
    public function safeDown()
    {
        $this->deleteServiceType();
        $this->dropServiceTypeParent();
    }

    /**
     * Создать поле в тип услуги
     */
    protected function addServiceTypeParent()
    {
        $tableName = ServiceType::tableName();
        $fieldName = 'parent_id';
        $this->addColumn($tableName, $fieldName, $this->integer());
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, ServiceType::tableName(), 'id', 'RESTRICT');
    }

    /**
     * Удалить поле из тип услуги
     */
    protected function dropServiceTypeParent()
    {
        $tableName = ServiceType::tableName();
        $fieldName = 'parent_id';
        $this->dropForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName);
        $this->dropColumn($tableName, $fieldName);
    }

    /**
     * Создать тип услуги
     */
    protected function addServiceType()
    {
        $tableName = ServiceType::tableName();
        $this->insert($tableName, [
            'id' => ServiceType::ID_VOIP_PACKAGE,
            'name' => 'Телефония. Пакеты',
        ]);
    }

    /**
     * Удалить тип услуги
     */
    protected function deleteServiceType()
    {
        $tableName = ServiceType::tableName();
        $this->delete($tableName, [
            'id' => ServiceType::ID_VOIP_PACKAGE,
        ]);
    }
}