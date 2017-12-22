<?php

use app\models\Datacenter;
use app\modules\uu\models\AccountTariff;

/**
 * Class m171222_121221_add_uu_infrastructure
 */
class m171222_121221_add_uu_infrastructure extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $accountTariffTableName = AccountTariff::tableName();
        $field = 'datacenter_id';
        $this->addColumn($accountTariffTableName, $field, $this->integer());
        $this->addForeignKey($field . '-fk', $accountTariffTableName, $field, Datacenter::tableName(), 'id');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $accountTariffTableName = AccountTariff::tableName();
        $field = 'datacenter_id';
        $this->dropForeignKey($field . '-fk', $accountTariffTableName);
        $this->dropColumn($accountTariffTableName, $field);
    }
}
