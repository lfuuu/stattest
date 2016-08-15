<?php

use app\models\TariffVoip;

class m160815_160636_status_7800_test extends \app\classes\Migration
{
    public function up()
    {
        $this->alterColumn(TariffVoip::tableName(), 'status', "enum('public','special','archive','7800','7800_test', 'test','operator','transit')");
    }

    public function down()
    {
        $this->update(TariffVoip::tableName(), ['status' => '7800'], ['status' => '7800_test']);
        $this->alterColumn(TariffVoip::tableName(), 'status', "enum('public','special','archive','7800','test','operator','transit')");
    }
}