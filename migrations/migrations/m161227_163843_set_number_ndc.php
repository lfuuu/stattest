<?php
use app\models\City;
use app\models\Country;
use app\models\Number;

/**
 * Class m161227_163843_set_number_ndc */
class m161227_163843_set_number_ndc extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $numberTableName = Number::tableName();
        $cityTableName = City::tableName();
        $countryTableName = Country::tableName();
        // NDC - 3 символа после кода страны
        // SUBSTR считает с 1, а не с 0
        $sql = <<<SQL
            UPDATE
                {$numberTableName} number,
                {$cityTableName} city,
                {$countryTableName} country
            SET
                number.ndc = SUBSTR(number.number, LENGTH(country.prefix) + 1, 3)
            WHERE
                number.city_id = city.id
                AND city.country_id = country.code
SQL;
        $this->execute($sql);

        $this->alterColumn($numberTableName, 'ndc', $this->integer()->notNull());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $numberTableName = Number::tableName();
        $this->alterColumn($numberTableName, 'ndc', $this->integer());
    }
}
