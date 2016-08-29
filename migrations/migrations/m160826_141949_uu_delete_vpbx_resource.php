<?php

use app\classes\uu\model\Resource;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\TariffResource;

class m160826_141949_uu_delete_vpbx_resource extends \app\classes\Migration
{
    public function up()
    {
        $tableName = TariffResource::tableName();
        $this->delete($tableName, ['resource_id' => 5]);

        $tableName = Resource::tableName();
        $this->delete($tableName, ['id' => 5]);
    }

    public function down()
    {
        $tableName = Resource::tableName();
        $this->insert($tableName, [
            'id' => 5,
            'name' => 'Звонки с сайта',
            'unit' => '',
            'min_value' => 0,
            'max_value' => 1,
            'service_type_id' => ServiceType::ID_VPBX,
        ]);
    }
}