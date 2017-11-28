<?php

use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;

/**
 * Class m171127_144913_add_infrastructure
 */
class m171127_144913_add_infrastructure extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $serviceTypeTableName = ServiceType::tableName();
        $this->insert($serviceTypeTableName, [
            'id' => ServiceType::ID_INFRASTRUCTURE,
            'name' => 'Инфраструктура',
        ]);

        $accountTariffTableName = AccountTariff::tableName();
        $this->addColumn($accountTariffTableName, 'infrastructure_project', $this->integer());
        $this->addColumn($accountTariffTableName, 'infrastructure_level', $this->integer());
        $this->addColumn($accountTariffTableName, 'price', $this->integer());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        ServiceType::deleteAll(['id' => ServiceType::ID_INFRASTRUCTURE]);

        $accountTariffTableName = AccountTariff::tableName();
        $this->dropColumn($accountTariffTableName, 'infrastructure_project');
        $this->dropColumn($accountTariffTableName, 'infrastructure_level');
        $this->dropColumn($accountTariffTableName, 'price');
    }
}
