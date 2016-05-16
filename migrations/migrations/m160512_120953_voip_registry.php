<?php

use app\models\City;
use app\models\ClientAccount;
use app\models\Country;
use app\models\NumberType;
use app\models\voip\Registry;

class m160512_120953_voip_registry extends \app\classes\Migration
{
    public function up()
    {
        $tableName = Registry::tableName();

        //$this->execute("DROP TABLE IF EXISTS " . $tableName);

        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            'country_id' => $this->integer(),
            'city_id' => $this->integer(),
            'source' => "enum('portability','operator','regulator') default 'portability'",
            'number_type_id' => $this->integer(),
            'number_from' => $this->string(32),
            'number_to' => $this->string(32),
            'account_id' => $this->integer(),
            'created_at' => $this->dateTime()
        ], 'ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8');

        $fieldName = 'country_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, Country::tableName(),
            'code', 'RESTRICT');

        $fieldName = 'city_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, City::tableName(),
            'id', 'RESTRICT');

        $fieldName = 'number_type_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, NumberType::tableName(),
            'id', 'RESTRICT');

        $fieldName = 'account_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, ClientAccount::tableName(),
            'id', 'RESTRICT');
/*
        $this->execute("insert into voip_registry select
                        null,
                        country_id,
                        city.id,
                        'operator',
                        number_type,
                        number_from,
                        number_to,
                        9130,
                        now()
                    from (
                        SELECT
                            region,
                            city_id,
                            SUBSTR(number FROM 1 FOR length(city_id)+3),
                            min(number) number_from,
                            max(number) number_to,
                            count(1) as count,
                            number_type
                        FROM `voip_numbers`
                        group by city_id, SUBSTR(number FROM 1 FOR length(city_id)+3)
                        having count >= 10
                    ) a, city
                    where a.city_id = city.id
                    ");
*/
    }

    public function down()
    {
        $this->dropTable(Registry::tableName());
    }
}