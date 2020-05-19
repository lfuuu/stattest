<?php

use app\models\ClientContragent;

/**
 * Class m200519_121523_contragent_add_branch_code
 */
class m200519_121523_contragent_add_branch_code extends \app\classes\Migration
{
    protected static $column = 'branch_code';
    protected static $columnBefore = 'kpp';

    public $tableName;

    /**
     * Up
     */
    public function safeUp()
    {
        $this->tableName = ClientContragent::tableName();

        $this->addColumn(
            $this->tableName,
            self::$column,
            $this
                ->string(3)
                ->null()
                ->after(self::$columnBefore)
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->tableName = ClientContragent::tableName();

        $this->dropColumn($this->tableName, self::$column);
    }
}
