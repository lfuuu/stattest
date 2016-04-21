<?php

use app\classes\uu\model\Tariff;

class m160411_134600_add_tariff_is_default extends \app\classes\Migration
{
    public function safeUp()
    {
        $tableName = Tariff::tableName();
        $fieldName = 'is_default';
        $this->addColumn($tableName, $fieldName, $this->integer());

        $delta = Tariff::DELTA_VOIP;
        $tariffVoipTableName = \app\models\TariffVoip::tableName();
        $this->execute("
            UPDATE {$tableName}, {$tariffVoipTableName}
            SET {$tableName}.is_default = 1
            WHERE {$tableName}.id = {$tariffVoipTableName}.id + {$delta}
            AND {$tariffVoipTableName}.is_testing > 0
        ");
    }

    public function safeDown()
    {
        $tableName = Tariff::tableName();
        $fieldName = 'is_default';
        $this->dropColumn($tableName, $fieldName);
    }
}