<?php

use app\models\Country;

class m160401_193300_add_country_prefix extends \app\classes\Migration
{
    public function safeUp()
    {
        $tableName = Country::tableName();
        $fieldName = 'prefix';
        $this->addColumn($tableName, $fieldName, $this->integer());

        Country::updateAll(['prefix' => 7], ['code' => Country::RUSSIA]);
        Country::updateAll(['prefix' => 49, 'lang' => 'de-DE'], ['code' => Country::GERMANY]);
        Country::updateAll(['prefix' => 36], ['code' => Country::HUNGARY]);
    }

    public function safeDown()
    {
        $tableName = Country::tableName();
        $fieldName = 'prefix';
        $this->dropColumn($tableName, $fieldName);
    }
}