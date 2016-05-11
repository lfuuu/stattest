<?php

use app\models\Country;

class m160511_103521_countries_append_site extends \app\classes\Migration
{
    public function up()
    {
        $this->addColumn(Country::tableName(), 'site', $this->string(255));
    }

    public function down()
    {
        $this->dropColumn(Country::tableName(), 'site');
    }
}