<?php

use app\classes\uu\model\Resource;
use app\classes\uu\model\TariffResource;

class m161214_105238_traf_flows_report_delete extends \app\classes\Migration
{
    public function up()
    {
        $this->execute('DROP TABLE IF EXISTS traf_flows_report');
        $this->delete(TariffResource::tableName(), ['resource_id' => [11, 12]]);
        $this->delete(Resource::tableName(), ['id' => [11, 12]]);
        $this->update(Resource::tableName(), ['name' => 'Трафик'], ['id' => Resource::ID_COLLOCATION_TRAFFIC]);
    }

    public function down()
    {
        //Nothing. No revert. One-way ticket
        return true;
    }
}