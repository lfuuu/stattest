<?php

use app\classes\uu\model\AccountTariff;
use app\models\ActualNumber;
use app\models\ActualVirtpbx;
use app\models\ClientAccount;
use app\models\Number;

class m160907_182850_uu_voip_status extends \app\classes\Migration
{
    private $fieldVoipNumber = 'voip_number';
    private $fieldTariffPeriodId = 'tariff_period_id';
    private $fieldIsUpdated = 'is_updated';

    public function up()
    {
        $table = AccountTariff::tableName();
        $this->createIndex('fk-' . $table . '-' . $this->fieldVoipNumber . '-' . $this->fieldTariffPeriodId, $table, [$this->fieldVoipNumber, $this->fieldTariffPeriodId]);
        $this->addColumn(Number::tableName(), 'uu_account_tariff_id', $this->integer(11)->defaultValue(null)->after('usage_id'));

        $this->addColumn($table, $this->fieldIsUpdated, $this->integer(1)->notNull()->defaultValue(0));
        $this->createIndex($table . '_' . $this->fieldIsUpdated, $table, $this->fieldIsUpdated);

        $this->addColumn(ActualNumber::tableName(), 'biller_version', $this->integer(1)->notNull()->defaultValue(ClientAccount::VERSION_BILLER_USAGE));
        $this->addColumn(ActualVirtpbx::tableName(), 'biller_version', $this->integer(1)->notNull()->defaultValue(ClientAccount::VERSION_BILLER_USAGE));
    }

    public function down()
    {
        $table = AccountTariff::tableName();
        $this->dropIndex('fk-' . $table . '-' . $this->fieldVoipNumber . '-' . $this->fieldTariffPeriodId, $table);
        $this->dropColumn(Number::tableName(), 'uu_account_tariff_id');

        $this->dropIndex($table . '_' . $this->fieldIsUpdated, $table);
        $this->dropColumn($table, $this->fieldIsUpdated);

        $this->dropColumn(ActualNumber::tableName(), 'biller_version');
        $this->dropColumn(ActualVirtpbx::tableName(), 'biller_version');
    }
}