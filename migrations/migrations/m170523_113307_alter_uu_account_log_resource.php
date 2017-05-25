<?php
use app\modules\uu\models\AccountLogResource;
use app\modules\uu\models\AccountTariffResourceLog;

/**
 * Class m170523_113307_alter_uu_account_log_resource
 */
class m170523_113307_alter_uu_account_log_resource extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $tableName = AccountLogResource::tableName();

        $this->addColumn($tableName, 'account_tariff_resource_log_id', $this->integer());
        $this->addForeignKey('fk-account_tariff_resource_log_id', $tableName, 'account_tariff_resource_log_id', AccountTariffResourceLog::tableName(), 'id', 'RESTRICT', 'RESTRICT');

        $this->createIndex('uidx-' . $tableName, $tableName, ['account_tariff_id', 'date_from', 'tariff_resource_id', 'account_tariff_resource_log_id'], $unique = true);
        $this->dropIndex('uidx-' . $tableName . '-account_tariff-date-resource', $tableName);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $tableName = AccountLogResource::tableName();

        $this->createIndex('uidx-' . $tableName . '-account_tariff-date-resource', $tableName, ['account_tariff_id', 'date_from', 'tariff_resource_id'], $unique = true);
        $this->dropIndex('uidx-' . $tableName, $tableName);

        $this->dropForeignKey('fk-account_tariff_resource_log_id', $tableName);
        $this->dropColumn($tableName, 'account_tariff_resource_log_id');

    }
}
