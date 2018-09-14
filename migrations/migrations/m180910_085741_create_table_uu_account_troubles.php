<?php

use app\modules\uu\models\AccountTrouble;

/**
 * Class m180910_085741_add_column_to_u_account_tariff
 */
class m180910_085741_create_table_uu_account_troubles extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $tableName = AccountTrouble::tableName();

        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            'account_tariff_id' => $this->integer()->notNull(),
            'trouble_id' => $this->integer()->notNull(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->createIndex('account_tariff_id_idx', $tableName, 'account_tariff_id', true);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(AccountTrouble::tableName());
    }
}
