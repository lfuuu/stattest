<?php

use app\classes\uu\model\ServiceType;

class m160720_163731_uu_welltime extends \app\classes\Migration
{
    public function up()
    {
        $tableName = ServiceType::tableName();
        $this->insert($tableName, [
            'id' => ServiceType::ID_WELLTIME_SAAS,
            'name' => 'Welltime как сервис',
        ]);

        ServiceType::updateAll(
            ['name' => 'Welltime как продукт'],
            ['id' => ServiceType::ID_WELLTIME_PRODUCT]
        );
    }

    public function down()
    {
        $tableName = ServiceType::tableName();
        $this->delete($tableName, [
            'id' => ServiceType::ID_WELLTIME_SAAS,
        ]);

        ServiceType::updateAll(
            ['name' => 'Welltime'],
            ['id' => ServiceType::ID_WELLTIME_PRODUCT]
        );
    }
}