<?php

use app\classes\uu\model\Tariff;

class m160815_102323_alter_uu_tariff extends \app\classes\Migration
{
    public function up()
    {
        $tableName = Tariff::tableName();
        $fieldName = 'n_prolongation_periods';
        $this->dropColumn($tableName, $fieldName);
    }

    public function down()
    {
        $tableName = Tariff::tableName();
        $fieldName = 'n_prolongation_periods';
        $this->addColumn($tableName, $fieldName, $this->integer()->notNull()->defaultValue(0));
    }
}