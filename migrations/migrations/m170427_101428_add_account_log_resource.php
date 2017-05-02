<?php
use app\modules\uu\models\AccountLogResource;

/**
 * Class m170427_101428_add_account_log_resource
 */
class m170427_101428_add_account_log_resource extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        AccountLogResource::deleteAll();

        $accountLogResourceTableName = AccountLogResource::tableName();
        $this->renameColumn($accountLogResourceTableName, 'date', 'date_from');
        $this->addColumn($accountLogResourceTableName, 'date_to', $this->date()->notNull());
        $this->addColumn($accountLogResourceTableName, 'coefficient', $this->integer()->notNull());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $tableName = AccountLogResource::tableName();
        $this->renameColumn($tableName, 'date_from', 'date');
        $this->dropColumn($tableName, 'date_to');
        $this->dropColumn($tableName, 'coefficient');
    }
}
