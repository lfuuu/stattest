<?php

use app\models\CityBillingMethod;
use app\models\City;

class m160620_114229_cities_billing_method extends \app\classes\Migration
{
    public function up()
    {
        $tableName = CityBillingMethod::tableName();

        //$this->execute('DROP TABLE IF EXISTS ' . $tableName);

        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            'name' => $this->string(255),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addColumn(City::tableName(), 'billing_method_id', $this->integer(11)->defaultValue(null));
        $this->addForeignKey(
            'fk-city-billing_method',
            City::tableName(),
            'billing_method_id',
            $tableName,
            'id',
            $delete = 'SET NULL'
        );
    }

    public function down()
    {
        $this->dropForeignKey('fk-city-billing_method', City::tableName());
        $this->dropColumn(City::tableName(), 'billing_method_id');
        $this->dropTable(CityBillingMethod::tableName());
    }
}