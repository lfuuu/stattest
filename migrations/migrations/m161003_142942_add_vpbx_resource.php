<?php

use app\classes\uu\model\Resource;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffResource;

class m161003_142942_add_vpbx_resource extends \app\classes\Migration
{
    public function up()
    {
        $tableName = Resource::tableName();
        $this->insert($tableName, [
            'id' => Resource::ID_VPBX_MIN_ROUTE,
            'name' => 'Маршрутизация по минимальной цене',
            'unit' => '',
            'min_value' => 0,
            'max_value' => 1,
            'service_type_id' => ServiceType::ID_VPBX,
        ]);
        $this->insert($tableName, [
            'id' => Resource::ID_VPBX_GEO_ROUTE,
            'name' => 'Маршрутизация по географии',
            'unit' => '',
            'min_value' => 0,
            'max_value' => 1,
            'service_type_id' => ServiceType::ID_VPBX,
        ]);

        $tableName = TariffResource::tableName();
        $activeQuery = Resource::find()->where(['>=', 'id', Tariff::DELTA]);
        /** @var Tariff $tariff */
        foreach ($activeQuery->each() as $tariff) {
            $this->insert($tableName, [
                'amount' => 0,
                'price_per_unit' => 400,
                'price_min' => 0,
                'resource_id' => Resource::ID_VPBX_MIN_ROUTE,
                'tariff_id' => $tariff->id,
            ]);
            $this->insert($tableName, [
                'amount' => 0,
                'price_per_unit' => 300,
                'price_min' => 0,
                'resource_id' => Resource::ID_VPBX_GEO_ROUTE,
                'tariff_id' => $tariff->id,
            ]);
        }
    }

    public function down()
    {
        $tableName = TariffResource::tableName();
        $this->delete($tableName, ['resource_id' => [Resource::ID_VPBX_MIN_ROUTE, Resource::ID_VPBX_GEO_ROUTE]]);

        $tableName = Resource::tableName();
        $this->delete($tableName, ['id' => [Resource::ID_VPBX_MIN_ROUTE, Resource::ID_VPBX_GEO_ROUTE]]);
    }
}