<?php

use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\ServiceType;
use app\models\City;

class m160330_153500_add_account_tariff extends \app\classes\Migration
{
    /**
     * Накатить
     */
    public function safeUp()
    {
        $tableName = AccountTariff::tableName();

        $fieldName = 'city_id';
        $this->addColumn($tableName, $fieldName, $this->integer());
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, City::tableName(), 'id', 'RESTRICT');

        $fieldName = 'voip_number';
        $this->addColumn($tableName, $fieldName, $this->string(15));

        $this->convertAccountTariff();
    }

    public function convertAccountTariff()
    {
        $deltaVoipAccountTariff = AccountTariff::DELTA_VOIP;
        $serviceTypeIdVoip = ServiceType::ID_VOIP;
        $accountTariffTableName = AccountTariff::tableName();

        $this->execute("UPDATE
            {$accountTariffTableName} account_tariff,
            usage_voip
        SET 
           account_tariff.voip_number = usage_voip.E164
        WHERE
            account_tariff.service_type_id = {$serviceTypeIdVoip}
            AND account_tariff.id = usage_voip.id + {$deltaVoipAccountTariff}
            AND usage_voip.E164 <> '0'
    ");
    }

    /**
     * Откатить
     */
    public function safeDown()
    {
        $tableName = AccountTariff::tableName();

        $fieldName = 'city_id';
        $this->dropForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName);
        $this->dropColumn($tableName, $fieldName);

        $fieldName = 'voip_number';
        $this->dropColumn($tableName, $fieldName);
    }
}