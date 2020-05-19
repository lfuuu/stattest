<?php

use app\modules\sbisTenzor\models\SBISContractor;

/**
 * Class m200519_131157_sbis_contractor_add_branch_logic
 */
class m200519_131157_sbis_contractor_add_branch_logic extends \app\classes\Migration
{
    protected static $column1 = 'branch_code';
    protected static $column1Before = 'full_name';

    protected static $column2 = 'fixed_exchange_id';
    protected static $column2Before = 'exchange_id';

    protected static $column3 = 'accounts';
    protected static $column3Before = 'account_id';

    public $tableName;

    /**
     * Up
     */
    public function safeUp()
    {
        $this->tableName = SBISContractor::tableName();

        $this->addColumn(
            $this->tableName,
            self::$column1,
            $this
                ->string(3)
                ->null()
                ->after(self::$column1Before)
        );

        $this->addColumn(
            $this->tableName,
            self::$column2,
            $this
                ->string(46)
                ->null()
                ->after(self::$column2Before)
        );

        $this->addColumn(
            $this->tableName,
            self::$column3,
            $this
                ->text()
                ->null()
                ->after(self::$column3Before)
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->tableName = SBISContractor::tableName();

        $this->dropColumn($this->tableName, self::$column3);
        $this->dropColumn($this->tableName, self::$column2);
        $this->dropColumn($this->tableName, self::$column1);
    }
}
