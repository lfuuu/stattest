<?php

use app\models\Country;

class m160825_130755_slovenia extends \app\classes\Migration
{
    public function safeUp()
    {
        $table = Country::tableName();
        $this->addColumn($table, 'order', $this->integer(1)->notNull()->defaultValue(0));

        $this->update($table, ['order' => 1], ['code' => Country::RUSSIA]);
        $this->update($table, ['order' => 2], ['code' => Country::HUNGARY]);
        $this->update($table, ['order' => 3], ['code' => Country::GERMANY]);
        $this->update($table, [
            'alpha_3' => 'SVK',
            'name' => 'Slovensko',
            'lang' => 'sk-SK',
            'currency_id' => 'EUR',
            'prefix' => 42,
            'in_use' => 1,
            'order' => 4
        ], [
            'code' => Country::SLOVAKIA
        ]);
    }

    public function safeDown()
    {
        $this->dropColumn(Country::tableName(), 'order');
    }
}