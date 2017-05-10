<?php
use app\models\User;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\Resource;

/**
 * Class m170510_152827_add_uu_resource_fk
 */
class m170510_152827_add_uu_resource_fk extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $accountTariffResourceLogTableName = AccountTariffResourceLog::tableName();
        $accountTariffTableName = AccountTariff::tableName();

        // сделать правильный тип
        $sql = <<<SQL
            ALTER TABLE {$accountTariffResourceLogTableName} ENGINE='InnoDB' COLLATE 'utf8_general_ci'
SQL;
        $this->execute($sql);

        // удалить битые данные
        $sql = <<<SQL
            DELETE FROM {$accountTariffResourceLogTableName}
            WHERE account_tariff_id NOT IN (SELECT id FROM {$accountTariffTableName})
SQL;
        $this->execute($sql);

        $this->addForeignKey('fk-account_tariff_id', $accountTariffResourceLogTableName, 'account_tariff_id', AccountTariff::tableName(), 'id', 'CASCADE', 'RESTRICT');
        $this->addForeignKey('fk-resource_id', $accountTariffResourceLogTableName, 'resource_id', Resource::tableName(), 'id', 'RESTRICT', 'RESTRICT');
        $this->addForeignKey('fk-insert_user_id', $accountTariffResourceLogTableName, 'insert_user_id', User::tableName(), 'id', 'SET NULL', 'SET NULL');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $accountTariffResourceLogTableName = AccountTariffResourceLog::tableName();
        $this->dropForeignKey('fk-account_tariff_id', $accountTariffResourceLogTableName);
        $this->dropForeignKey('fk-resource_id', $accountTariffResourceLogTableName);
        $this->dropForeignKey('fk-insert_user_id', $accountTariffResourceLogTableName);
    }
}
