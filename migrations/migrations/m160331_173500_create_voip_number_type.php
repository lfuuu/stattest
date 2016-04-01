<?php

use app\models\Country;
use app\models\Number;
use app\models\NumberType;
use app\models\NumberTypeCountry;

class m160331_173500_create_voip_number_type extends \app\classes\Migration
{
    /**
     * Накатить
     */
    public function safeUp()
    {
        $this->createNumberType();
        $this->createNumberTypeCountry();
        $this->addNumberFk();
    }

    /**
     * создать NumberType
     */
    private function createNumberType()
    {
        $tableName = NumberType::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            'name' => $this->string(255),
        ]);

        $this->batchInsert($tableName, ['id', 'name'], [
            [NumberType::ID_GEO_DID, 'Национальный географический'],
            [NumberType::ID_NON_GEO_DID, 'Национальный негеографический'],
            [NumberType::ID_INTERNATIONAL_DID, 'Международный'],
            [NumberType::ID_INTERNAL, 'Внутренний'],
            [NumberType::ID_EXTERNAL, 'Внешний'],
        ]);
    }

    /**
     * создать NumberTypeCountry
     * @link http://rd.welltime.ru/confluence/pages/viewpage.action?pageId=10715171
     */
    public function createNumberTypeCountry()
    {
        $tableName = NumberTypeCountry::tableName();
        $this->createTable($tableName, [
            'voip_number_type_id' => $this->integer(),
            'country_id' => $this->integer(4),
        ]);

        $this->addPrimaryKey('pk-' . $tableName, $tableName, ['voip_number_type_id', 'country_id']);

        $fieldName = 'voip_number_type_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, NumberType::tableName(), 'id', 'CASCADE');

        $fieldName = 'country_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, Country::tableName(), 'code', 'RESTRICT');

        $this->batchInsert($tableName, ['voip_number_type_id', 'country_id'], [
            [NumberType::ID_GEO_DID, Country::RUSSIA],
            [NumberType::ID_GEO_DID, Country::HUNGARY],
            [NumberType::ID_GEO_DID, Country::GERMANY],

            [NumberType::ID_NON_GEO_DID, Country::HUNGARY],
            [NumberType::ID_NON_GEO_DID, Country::GERMANY],

            [NumberType::ID_INTERNATIONAL_DID, Country::HUNGARY],
            [NumberType::ID_INTERNATIONAL_DID, Country::GERMANY],

            [NumberType::ID_INTERNAL, Country::HUNGARY],
            [NumberType::ID_INTERNAL, Country::GERMANY],

            [NumberType::ID_EXTERNAL, Country::RUSSIA],
        ]);
    }

    /**
     * создать FK
     */
    public function addNumberFk()
    {
        $tableName = Number::tableName();
        $fieldName = 'number_type';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, NumberType::tableName(), 'id', 'RESTRICT');
    }

    /**
     * удалить FK
     */
    public function dropNumberFk()
    {
        $tableName = Number::tableName();
        $fieldName = 'number_type';
        $this->dropForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName);
    }

    /**
     * Откатить
     */
    public function safeDown()
    {
        $this->dropNumberFk();
        $this->dropTable(NumberTypeCountry::tableName());
        $this->dropTable(NumberType::tableName());
    }
}