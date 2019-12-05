<?php

use app\models\ClientAccount;
use app\modules\sbisTenzor\models\SBISContractor;

/**
 * Class m191202_170501_add_sbis_contractor_roaming
 */
class m191202_170501_add_sbis_contractor_roaming extends \app\classes\Migration
{
    protected static $columnName = 'is_roaming';
    protected static $clientColumnName = 'account_id';

    public $tableName;
    public $tableNameClient;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->tableName = SBISContractor::tableName();
        $this->tableNameClient = ClientAccount::tableName();

        $this->addColumn(
            $this->tableName,
            self::$columnName,
            $this
                ->boolean()
                ->notNull()
                ->defaultValue(false)
                ->after('full_name')
        );

        $this->addColumn(
            $this->tableName,
            self::$clientColumnName,
            $this
                ->integer(11)
                ->after('id')
        );

        // foreign keys
        $this->addForeignKey(
            'fk-' . $this->tableName . '-' . self::$clientColumnName,
            $this->tableName, self::$clientColumnName,
            $this->tableNameClient, 'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->tableName = SBISContractor::tableName();

        // foreign keys
        $this->dropForeignKey(
            'fk-' . $this->tableName . '-' . self::$clientColumnName,
            $this->tableName
        );

        $this->dropColumn($this->tableName, self::$clientColumnName);
        $this->dropColumn($this->tableName, self::$columnName);
    }
}
