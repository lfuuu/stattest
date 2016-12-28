<?php
use app\models\City;
use app\models\Number;

/**
 * Class m161227_162517_set_number_country_code */
class m161227_162517_set_number_country_code extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $numberTableName = Number::tableName();
        $cityTableName = City::tableName();
        $sql = <<<SQL
            UPDATE
                {$numberTableName} number,
                {$cityTableName} city
            SET
                number.country_code = city.country_id
            WHERE
                number.city_id = city.id
SQL;
        $this->execute($sql);

        $this->alterColumn($numberTableName, 'country_code', $this->integer()->notNull());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $numberTableName = Number::tableName();
        $this->alterColumn($numberTableName, 'country_code', $this->integer());
    }
}
