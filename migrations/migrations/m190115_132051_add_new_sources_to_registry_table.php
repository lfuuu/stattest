<?php

use app\models\voip\Registry;

/**
 * Class m190115_132051_add_new_sources_to_registry_table
 */
class m190115_132051_add_new_sources_to_registry_table extends \app\classes\Migration
{
    private $tableName;
    private $columnName;

    public function init()
    {
        parent::init();
        $this->tableName = Registry::tableName();
        $this->columnName = 'source';
    }
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn($this->tableName, $this->columnName, "enum('portability','operator','regulator','innonet','boxbone','portability_not_for_sale','operator_not_for_sale') DEFAULT 'portability'");
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn($this->tableName, $this->columnName, "enum('portability','operator','regulator','portability_not_for_sale','operator_not_for_sale') DEFAULT 'portability'");
    }
}
