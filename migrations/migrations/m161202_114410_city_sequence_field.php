<?php

use app\models\City;

class m161202_114410_city_sequence_field extends \app\classes\Migration
{
    public function up()
    {
        $tableName = City::tableName();

        $this->addColumn($tableName, 'order', $this->integer()->defaultValue(0));
    }

    public function down()
    {
        $tableName = City::tableName();

        $this->dropColumn($tableName, 'order');
    }
}