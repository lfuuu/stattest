<?php

use app\models\City;
use app\models\Country;
use app\models\TariffNumber;

class m160512_095548_tarifs_number_append_fk extends \app\classes\Migration
{
    public function up()
    {
        $this->addForeignKey(
            'fk-tarifs_number-city_id',
            TariffNumber::tableName(),
            'city_id',
            City::tableName(),
            'id'
        );

        $this->addForeignKey(
            'fk-tarifs_number-country_id',
            TariffNumber::tableName(),
            'country_id',
            Country::tableName(),
            'code'
        );
    }

    public function down()
    {
        $this->dropForeignKey('fk-tarifs_number-city_id', TariffNumber::tableName());
        $this->dropForeignKey('fk-tarifs_number-country_id', TariffNumber::tableName());
    }
}