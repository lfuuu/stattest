<?php

use app\models\TariffNumber;

class m160512_085950_tarifs_number_remove_fields extends \app\classes\Migration
{
    public function up()
    {
        $this->dropColumn(TariffNumber::tableName(), 'connection_point_id');
        $this->dropColumn(TariffNumber::tableName(), 'periodical_fee');
    }

    public function down()
    {
        $this->addColumn(TariffNumber::tableName(), 'connection_point_id', $this->integer(11)->notNull());
        $this->addColumn(TariffNumber::tableName(), 'periodical_fee', $this->decimal(10, 2)->notNull());
    }
}