<?php

use app\models\TariffInternet;
use app\models\TariffVirtpbx;

class m160725_090947_status_test extends \app\classes\Migration
{
    public function up()
    {
        $this->alterColumn(TariffVirtpbx::tableName(), 'status', "enum('public','special','archive','test') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'public'");
        $this->alterColumn(TariffInternet::tableName(), 'status', "enum('public','special','archive','test','adsl_su') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'public'");
    }

    public function down()
    {
        $this->alterColumn(TariffVirtpbx::tableName(), 'status', "enum('public','special','archive') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'public'");
        $this->alterColumn(TariffInternet::tableName(), 'status', "enum('public','special','archive','adsl_su') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'public'");
    }
}