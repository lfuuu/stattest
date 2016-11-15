<?php

use app\models\Business;
use app\models\BusinessProcess;
use app\models\BusinessProcessStatus;
use app\models\ClientAccount;
use app\models\Country;
use app\models\Currency;
use app\models\EntryPoint;
use app\models\LkWizardState;
use app\models\Organization;
use app\models\Region;

class m161115_110943_entry_point extends \app\classes\Migration
{
    public function up()
    {
        $this->createTable(EntryPoint::tableName(), [
            'id' => $this->primaryKey(),
            'code' => $this->string()->notNull(),
            'name' => $this->string()->notNull(),
            'super_client_prefix' => $this->string()->notNull()->defaultValue(''),
            'wizard_type' => $this->string()->notNull()->defaultValue(LkWizardState::TYPE_MCN),
            'country_id' => $this->integer()->notNull()->defaultValue(Country::RUSSIA),
            'region_id' => $this->integer()->notNull()->defaultValue(Region::MOSCOW),
            'organization_id' => $this->integer()->notNull()->defaultValue(Organization::MCM_TELEKOM),
            'client_contract_business_id' => $this->integer()->notNull()->defaultValue(Business::TELEKOM),
            'client_contract_business_process_id' => $this->integer()->notNull()->defaultValue(BusinessProcess::TELECOM_MAINTENANCE),
            'client_contract_business_process_status_id' => $this->integer()->notNull()->defaultValue(BusinessProcessStatus::TELEKOM_MAINTENANCE_ORDER_OF_SERVICES),
            'currency_id' => $this->string()->notNull()->defaultValue(Currency::RUB),
            'timezone_name' => $this->string()->defaultValue(null),
            'is_postpaid' => $this->integer()->notNull()->defaultValue(0),
            'account_version' => $this->integer()->notNull()->defaultValue(ClientAccount::VERSION_BILLER_UNIVERSAL),
            'credit' => $this->integer(),
            'voip_credit_limit_day' => $this->integer()->notNull()->defaultValue(0),
            'voip_limit_mn_day' => $this->integer()->notNull(0),
            'is_default' => $this->integer()->notNull()->defaultValue(0)
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $this->createIndex('uniq-code', EntryPoint::tableName(), 'code', true);
        $this->addForeignKey('fk-' . Country::tableName().'-code', EntryPoint::tableName(), 'country_id', Country::tableName(), 'code');
        $this->addForeignKey('fk-' . Region::tableName().'-id', EntryPoint::tableName(), 'region_id', Region::tableName(), 'id');
        $this->addForeignKey('fk-' . Organization::tableName() . '-id', EntryPoint::tableName(), 'organization_id', Organization::tableName(), 'id');
        $this->addForeignKey('fk-' . Business::tableName().'-id', EntryPoint::tableName(), 'client_contract_business_id', Business::tableName(), 'id');
        $this->addForeignKey('fk-' . BusinessProcess::tableName().'-id', EntryPoint::tableName(), 'client_contract_business_process_id', BusinessProcess::tableName(), 'id');
        $this->addForeignKey('fk-' . BusinessProcessStatus::tableName().'-id', EntryPoint::tableName(), 'client_contract_business_process_status_id', BusinessProcessStatus::tableName(), 'id');

        // default entry point
        $this->insert(EntryPoint::tableName(), [
            'id' => 1,
            'code' => 'RU1',
            'name' => 'Клиентская заявка с mcn.ru',
            'super_client_prefix' => 'Client #',
            'wizard_type' => 'mcn',
            'country_id' => Country::RUSSIA,
            'region_id' => Region::MOSCOW,
            'organization_id' => Organization::MCM_TELEKOM,
            'client_contract_business_id' => Business::TELEKOM,
            'client_contract_business_process_id' => BusinessProcess::TELECOM_MAINTENANCE,
            'client_contract_business_process_status_id' => BusinessProcessStatus::TELEKOM_MAINTENANCE_ORDER_OF_SERVICES,
            'currency_id' => Currency::RUB,
            'timezone_name' => Region::TIMEZONE_MOSCOW,
            'is_postpaid' => 1,
            'account_version' => ClientAccount::VERSION_BILLER_USAGE,
            'credit' => 0,
            'voip_credit_limit_day' => 2000,
            'voip_limit_mn_day' => 1000,
            'is_default' => 1,
        ]);
    }

    public function down()
    {
        $this->dropTable(EntryPoint::tableName());
    }
}