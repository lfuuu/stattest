<?php

use app\models\City;

class m160616_132123_is_use_city extends \app\classes\Migration
{
    public function up()
    {
        $this->addColumn(City::tableName(), 'in_use', $this->integer(1)->notNull()->defaultValue(0));
        
        City::dao()->markUseCities();
    }

    public function down()
    {
        $this->dropColumn(City::tableName(), 'in_use');
    }
}