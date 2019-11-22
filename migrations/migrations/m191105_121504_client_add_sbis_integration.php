<?php

use app\models\ClientAccount;
use app\modules\sbisTenzor\models\SBISExchangeGroup;

/**
 * Class m191105_121504_client_add_sbis_integration
 */
class m191105_121504_client_add_sbis_integration extends \app\classes\Migration
{
    public $tableName;
    public $tableNameGroup;

    /**
     * Up
     */
    public function safeUp()
    {
        $this->tableName = ClientAccount::tableName();
        $this->tableNameGroup = SBISExchangeGroup::tableName();

        $this->addColumn(
            $this->tableName,
            'exchange_group_id',
            $this->integer(11)
        );

        // foreign keys
        $this->addForeignKey(
            'fk-' . $this->tableName . '-' . 'exchange_group_id',
            $this->tableName, 'exchange_group_id',
            $this->tableNameGroup, 'id'
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->tableName = ClientAccount::tableName();

        $this->dropColumn($this->tableName, 'exchange_group_id');
    }
}
