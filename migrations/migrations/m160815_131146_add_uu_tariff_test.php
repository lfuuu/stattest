<?php

use app\classes\uu\model\TariffStatus;

class m160815_131146_add_uu_tariff_test extends \app\classes\Migration
{
    public function up()
    {
        $tableName = TariffStatus::tableName();
        $this->insert($tableName, [
            'id' => TariffStatus::ID_VOIP_8800_TEST,
            'name' => '8-800 тестовый',
        ]);
    }

    public function down()
    {
        $tableName = TariffStatus::tableName();
        $this->delete($tableName, [
            'id' => TariffStatus::ID_VOIP_8800_TEST,
        ]);
    }
}