<?php

use app\classes\uu\model\AccountTariff;
use app\models\City;

class m160612_124800_uu_convert_region_to_city extends \app\classes\Migration
{
    /**
     *
     */
    public function up()
    {
        $cityTableName = City::tableName();
        $accountTableName = AccountTariff::tableName();
        $sql = <<<SQL
UPDATE
    {$accountTableName} account_tariff,
    {$cityTableName} city
SET
    account_tariff.city_id = city.id
WHERE
    account_tariff.city_id IS NULL
    AND account_tariff.region_id = city.connection_point_id
SQL;
        $this->execute($sql);
    }

    public function down()
    {
    }
}