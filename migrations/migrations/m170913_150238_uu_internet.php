<?php

use app\modules\uu\models\Resource;
use app\modules\uu\models\ServiceType;

/**
 * Class m170913_150238_uu_internet
 */
class m170913_150238_uu_internet extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->_addServiceType();

        $this->insert(Resource::tableName(), [
            'id' => Resource::ID_VOIP_PACKAGE_INTERNET,
            'name' => 'Трафик',
            'unit' => 'Mb',
            'min_value' => 0,
            'max_value' => null,
            'service_type_id' => ServiceType::ID_VOIP_PACKAGE_INTERNET,
        ]);
    }

    /**
     * Добавить в ServiceType
     */
    private function _addServiceType()
    {
        $serviceTypeTableName = ServiceType::tableName();
        $this->insert($serviceTypeTableName, [
            'id' => ServiceType::ID_VOIP_PACKAGE_INTERNET,
            'name' => 'Телефония. Пакет интернета',
            'parent_id' => ServiceType::ID_VOIP,
            'close_after_days' => ServiceType::CLOSE_AFTER_DAYS,
        ]);

        $this->update($serviceTypeTableName, [
            'name' => 'Телефония. Пакет звонков',
            'parent_id' => ServiceType::ID_VOIP,
        ], [
            'id' => ServiceType::ID_VOIP_PACKAGE_CALLS,
        ]);

        $this->update($serviceTypeTableName, [
            'name' => 'Транк. Пакет оригинации',
            'parent_id' => ServiceType::ID_TRUNK,
        ], [
            'id' => ServiceType::ID_TRUNK_PACKAGE_ORIG,
        ]);

        $this->update($serviceTypeTableName, [
            'name' => 'Транк. Пакет терминации',
            'parent_id' => ServiceType::ID_TRUNK,
        ], [
            'id' => ServiceType::ID_TRUNK_PACKAGE_TERM,
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->delete(ServiceType::tableName(), ['id' => ServiceType::ID_VOIP_PACKAGE_INTERNET]);
        $this->delete(Resource::tableName(), ['id' => Resource::ID_VOIP_PACKAGE_INTERNET]);
    }
}
