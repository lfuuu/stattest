<?php

use app\models\City;
use app\models\Country;
use app\models\DidGroup;

class m161031_143812_alter_did_group extends \app\classes\Migration
{
    public function up()
    {
        $didGroupTableName = DidGroup::tableName();
        $cityTableName = City::tableName();

        $fieldName = 'country_code';
        $this->addColumn($didGroupTableName, $fieldName, $this->integer());

        $sql = <<<SQL
            UPDATE
                {$didGroupTableName}, {$cityTableName}
            SET
                {$didGroupTableName}.country_code = {$cityTableName}.country_id
            WHERE
                {$didGroupTableName}.city_id = {$cityTableName}.id
SQL;
        $this->db->createCommand($sql)
            ->execute();

        $this->alterColumn($didGroupTableName, $fieldName, $this->integer()->notNull());
        $this->addForeignKey('fk-' . $didGroupTableName . '-' . $fieldName, $didGroupTableName, $fieldName, Country::tableName(), 'code', 'RESTRICT');


        $this->alterColumn($didGroupTableName, 'city_id', $this->integer());
    }

    public function down()
    {
        $didGroupTableName = DidGroup::tableName();

        $fieldName = 'country_code';
        $this->dropForeignKey('fk-' . $didGroupTableName . '-' . $fieldName, $didGroupTableName);
        $this->dropColumn($didGroupTableName, $fieldName);
    }
}