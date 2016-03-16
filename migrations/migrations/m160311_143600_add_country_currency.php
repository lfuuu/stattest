<?php

use app\models\Country;
use app\models\Currency;

class m160311_143600_add_country_currency extends \app\classes\Migration
{
    public function safeUp()
    {
        $tableName = Country::tableName();
        $fieldName = 'currency_id';
        $this->addColumn($tableName, $fieldName, 'char(3) CHARACTER SET utf8 COLLATE utf8_bin');
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, Currency::tableName(), 'id', 'RESTRICT');

        Country::updateAll(['currency_id' => Currency::RUB], ['code' => Country::RUSSIA]);
        Country::updateAll(['currency_id' => Currency::EUR, 'lang' => 'de-DE'], ['code' => Country::GERMANY]);
        Country::updateAll(['currency_id' => Currency::HUF], ['code' => Country::HUNGARY]);
    }

    public function safeDown()
    {
        $tableName = Country::tableName();
        $fieldName = 'currency_id';
        $this->dropForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName);
        $this->dropColumn($tableName, $fieldName);

        Country::updateAll(['lang' => 'ru-RU'], ['code' => Country::GERMANY]);
    }
}