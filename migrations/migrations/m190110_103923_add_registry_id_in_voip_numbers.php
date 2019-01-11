<?php

use app\models\Number;
use app\models\voip\Registry;

/**
 * Class m190110_103923_add_registry_id_in_voip_numbers
 */
class m190110_103923_add_registry_id_in_voip_numbers extends \app\classes\Migration
{
    private $tableName;
    private $columnName;
    private $registryTableName;

    public function init()
    {
        parent::init();
        $this->tableName = Number::tableName();
        $this->registryTableName = Registry::tableName();
        $this->columnName = 'registry_id';
    }

    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, $this->columnName, $this->integer());
        $this->addForeignKey(
            "{$this->tableName}-{$this->registryTableName}-fk",
            $this->tableName,
            $this->columnName,
            $this->registryTableName,
            'id'
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropForeignKey("{$this->tableName}-{$this->registryTableName}-fk", $this->tableName);
        $this->dropColumn($this->tableName, $this->columnName);
    }
}
