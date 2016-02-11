<?php

use yii\db\Schema;

class m160201_124835_currency_short extends \app\classes\Migration
{
    public function up()
    {
        $this->execute('alter table currency add symbol varchar(16) not null default ""');//"$", "£", "€"

        $this->update('currency', ['symbol' => '$'], ['id' => 'USD']);
        $this->update('currency', ['symbol' => 'руб.'], ['id' => 'RUB']);
        $this->update('currency', ['symbol' => '€'], ['id' => 'EUR']);
        $this->update('currency', ['symbol' => 'Ft.'], ['id' => 'HUF']);
    }

    public function down()
    {
        echo "m160201_124835_currency_short cannot be reverted.\n";

        return false;
    }
}
