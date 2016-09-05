<?php

use app\classes\uu\model\Resource;
use app\classes\uu\model\ServiceType;

/**
 * Handles the creation for table `uu_vm_collocation`.
 */
class m160901_154713_add_uu_vm_collocation extends \app\classes\Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableName = ServiceType::tableName();
        $this->insert($tableName, [
            'id' => ServiceType::ID_VM_COLLOCATION,
            'name' => 'VM collocation',
        ]);

        $tableName = Resource::tableName();
        $this->insert($tableName, [
            'id' => Resource::ID_VM_COLLOCATION_PROCESSOR,
            'name' => 'Процессор',
            'min_value' => 0,
            'max_value' => null,
            'service_type_id' => ServiceType::ID_VM_COLLOCATION,
            'unit' => 'ГГц',
        ]);
        $this->insert($tableName, [
            'id' => Resource::ID_VM_COLLOCATION_HDD,
            'name' => 'Дисковое пространство',
            'min_value' => 0,
            'max_value' => null,
            'service_type_id' => ServiceType::ID_VM_COLLOCATION,
            'unit' => 'Мб.',
        ]);
        $this->insert($tableName, [
            'id' => Resource::ID_VM_COLLOCATION_RAM,
            'name' => 'Оперативная память',
            'min_value' => 0,
            'max_value' => null,
            'service_type_id' => ServiceType::ID_VM_COLLOCATION,
            'unit' => 'Мб.',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $tableName = Resource::tableName();
        $this->delete($tableName, ['id' => [Resource::ID_VM_COLLOCATION_PROCESSOR, Resource::ID_VM_COLLOCATION_HDD, Resource::ID_VM_COLLOCATION_RAM]]);

        $tableName = ServiceType::tableName();
        $this->delete($tableName, [
            'id' => ServiceType::ID_VM_COLLOCATION,
        ]);
    }
}
