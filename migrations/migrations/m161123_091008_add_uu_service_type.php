<?php

use app\classes\uu\model\Resource;
use app\classes\uu\model\ServiceType;

class m161123_091008_add_uu_service_type extends \app\classes\Migration
{
    public function up()
    {
        $serviceTypeTableName = ServiceType::tableName();
        $this->batchInsert($serviceTypeTableName, ['id', 'name'], [
            [ServiceType::ID_TRUNK, 'Транк'],
            [ServiceType::ID_TRUNK_PACKAGE_ORIG, 'ОригТранк. Пакеты'],
            [ServiceType::ID_TRUNK_PACKAGE_TERM, 'ТермТранк. Пакеты'],
        ]);

        $tableName = Resource::tableName();
        $this->insert($tableName, [
            'id' => Resource::ID_TRUNK_CALLS,
            'name' => 'Звонки',
            'unit' => '¤',
            'min_value' => 0,
            'max_value' => null,
            'service_type_id' => ServiceType::ID_TRUNK,
        ]);
    }

    public function down()
    {
        $serviceTypeIds = [ServiceType::ID_TRUNK, ServiceType::ID_TRUNK_PACKAGE_ORIG, ServiceType::ID_TRUNK_PACKAGE_TERM];

        $tableName = Resource::tableName();
        $this->delete($tableName, ['id' => $serviceTypeIds]);

        $tableName = ServiceType::tableName();
        $this->delete($tableName, ['IN', 'id', $serviceTypeIds]);
    }
}