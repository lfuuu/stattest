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
        // uu_account_tariff
        $table = AccountTariff::tableName();
        $this->createIndex('idx-' . $table . '-' . $this->fieldVoipNumber . '-' . $this->fieldTariffPeriodId, $table, [$this->fieldVoipNumber, $this->fieldTariffPeriodId]);
        $this->addColumn($table, $this->fieldIsUpdated, $this->integer()->notNull()->defaultValue(0));
        $this->createIndex($table . '_' . $this->fieldIsUpdated, $table, $this->fieldIsUpdated);

        // voip_number
        $this->addColumn(Number::tableName(), 'uu_account_tariff_id', $this->integer()->defaultValue(null)->after('usage_id'));
        $this->addForeignKey('fk-' . $table . '-' . 'id', Number::tableName(), 'uu_account_tariff_id', $table, 'id', 'SET NULL');

        // biller_version in actual tables
        $this->addColumn(ActualNumber::tableName(), 'biller_version', $this->smallInteger()->notNull()->defaultValue(ClientAccount::VERSION_BILLER_USAGE));
        $this->addColumn(ActualVirtpbx::tableName(), 'biller_version', $this->smallInteger()->notNull()->defaultValue(ClientAccount::VERSION_BILLER_USAGE));
    }

    public function down()
    {
        // uu_account_tariff
        $table = AccountTariff::tableName();
        $this->dropIndex('idx-' . $table . '-' . $this->fieldVoipNumber . '-' . $this->fieldTariffPeriodId, $table);
        $this->dropColumn($table, $this->fieldIsUpdated);

        // voip_numbers
        $this->dropForeignKey('fk-' . $table . '-' . 'id', Number::tableName());
        $this->dropColumn(Number::tableName(), 'uu_account_tariff_id');

        // biller_version in actual tables
        $this->dropColumn(ActualNumber::tableName(), 'biller_version');
        $this->dropColumn(ActualVirtpbx::tableName(), 'biller_version');
    }
}