<?php

use app\classes\uu\model\AccountEntry;
use app\classes\uu\model\AccountLogMin;
use app\classes\uu\model\AccountLogPeriod;
use app\classes\uu\model\AccountLogResource;
use app\classes\uu\model\AccountLogSetup;
use app\classes\uu\model\Bill;
use app\models\ClientAccount;

class m161104_105752_alter_uu_account_log extends \app\classes\Migration
{
    public function up()
    {
        $sqls = [
            'TRUNCATE ' . AccountLogSetup::tableName(),
            'TRUNCATE ' . AccountLogPeriod::tableName(),
            'TRUNCATE ' . AccountLogResource::tableName(),
            'TRUNCATE ' . AccountLogMin::tableName(),
            'DELETE FROM ' . AccountEntry::tableName(),
            'DELETE FROM ' . Bill::tableName(),
            'DELETE FROM ' . \app\models\Bill::tableName() . ' WHERE biller_version = ' . ClientAccount::VERSION_BILLER_UNIVERSAL,
        ];
        foreach ($sqls as $sql) {
            $this->execute($sql);
        }

        $tableName = AccountLogResource::tableName();
        $this->createIndex('uidx-uu_account_log_resource-account_tariff-date-resource', $tableName, ['account_tariff_id', 'date', 'tariff_resource_id'], true);
//        $this->dropIndex('fk-uu_account_log_resource-account_tariff_id', $tableName); // индекс для FK уже не нужен, ибо вышеуказанный его покрывает
    }

    public function down()
    {
        $tableName = AccountLogResource::tableName();
        $this->createIndex('fk-uu_account_log_resource-account_tariff_id', $tableName, ['account_tariff_id']);
        $this->dropIndex('uidx-uu_account_log_resource-account_tariff-date-resource', $tableName);
    }
}