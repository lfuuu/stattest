<?php

use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\Tariff;

class m160804_091857_alter_uu_tariff extends \app\classes\Migration
{
    public function up()
    {
        $tariffTableName = Tariff::tableName();
        $tariffDelta = Tariff::DELTA;
        $this->execute("ALTER TABLE {$tariffTableName} AUTO_INCREMENT = {$tariffDelta}");

        $accountTariffTableName = AccountTariff::tableName();
        $accountTariffDelta = AccountTariff::DELTA;
        $this->execute("ALTER TABLE {$accountTariffTableName} AUTO_INCREMENT = {$accountTariffDelta}");
    }

    public function down()
    {
    }
}