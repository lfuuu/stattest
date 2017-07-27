<?php
use app\models\ClientSubAccount;

/**
 * Class m170726_065622_subaccount
 */
class m170726_065622_subaccount extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $intNotNullDefault0 = $this->integer()->notNull()->defaultValue(0);

        $table = ClientSubAccount::tableName();

        $this->createTable(ClientSubAccount::tableName(), [
            "id" => $this->integer()->notNull(),
            "account_id" => $this->integer()->notNull(),
            "sub_account" => $this->string(128)->notNull()->defaultValue(''),
            "stat_product_id" => $this->integer()->notNull(),
            "name" => $this->string(256)->notNull()->defaultValue(''),
            "balance" => $this->decimal(12, 2)->notNull()->defaultValue(0),
            "credit" => $this->decimal(12, 2)->notNull()->defaultValue(0),
            "amount_date" => $this->dateTime(),
            "voip_limit_day" => $intNotNullDefault0,
            "voip_limit_month" => $intNotNullDefault0,
            "voip_limit_mn_day" => $intNotNullDefault0,
            "voip_limit_mn_month" => $intNotNullDefault0,
            "is_voip_orig_disabled" => $intNotNullDefault0,
            "is_voip_blocked" => $intNotNullDefault0,
            "number" => $intNotNullDefault0
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $this->addPrimaryKey('pk-id', ClientSubAccount::tableName(), 'id');

        $this->execute("DROP TRIGGER IF EXISTS to_postgres_{$table}_after_insert");
        $sql = <<<SQL
            CREATE TRIGGER `to_postgres_client_subaccount_after_insert` AFTER INSERT ON `{$table}` FOR EACH ROW BEGIN
                call z_sync_postgres('{$table}', NEW.id);
            END;
SQL;
        $this->execute($sql);

        $this->execute("DROP TRIGGER IF EXISTS to_postgres_{$table}_after_delete");
        $sql = <<<SQL
            CREATE TRIGGER `to_postgres_client_subaccount_after_delete` AFTER DELETE ON `{$table}` FOR EACH ROW BEGIN
                call z_sync_postgres('{$table}', OLD.id);
            END;
SQL;
        $this->execute($sql);

        $this->execute("DROP TRIGGER IF EXISTS to_postgres_{$table}_after_update");

        $sql = <<<SQL
            CREATE TRIGGER to_postgres_{$table}_after_update AFTER UPDATE ON {$table} FOR EACH ROW BEGIN
                if
                    NEW.account_id <> OLD.account_id
                    OR NEW.sub_account <> OLD.sub_account
                    OR NEW.number <> OLD.number
                    OR NEW.stat_product_id <> OLD.stat_product_id
                    OR NEW.balance <> OLD.balance
                    OR NEW.credit <> OLD.credit
                    OR NEW.amount_date <> OLD.amount_date
                    OR NEW.voip_limit_month <> OLD.voip_limit_month
                    OR NEW.voip_limit_day <> OLD.voip_limit_day
                    OR NEW.voip_limit_mn_day <> OLD.voip_limit_mn_day
                    OR NEW.voip_limit_mn_month <> OLD.voip_limit_mn_month
                    OR NEW.is_voip_orig_disabled <> OLD.is_voip_orig_disabled
                    OR NEW.is_voip_blocked <> OLD.is_voip_blocked
                THEN
                    call z_sync_postgres("{$table}", NEW.id);
                END IF;
            END;
SQL;
        $this->execute($sql);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(ClientSubAccount::tableName());
    }
}
