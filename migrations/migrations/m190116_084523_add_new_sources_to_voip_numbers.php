<?php

use app\models\Number;

/**
 * Class m190116_084523_add_new_sources_to_voip_numbers
 */
class m190116_084523_add_new_sources_to_voip_numbers extends \app\classes\Migration
{
    private $tableName;
    private $columnName;

    public function init()
    {
        parent::init();
        $this->tableName = Number::tableName();
        $this->columnName = 'source';
    }

    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn($this->tableName, $this->columnName, "enum('portability','operator','regulator','innonet','boxbone','portability_not_for_sale','operator_not_for_sale') DEFAULT 'operator'");
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn($this->tableName, $this->columnName, "enum('portability','operator','regulator','portability_not_for_sale','operator_not_for_sale') DEFAULT 'operator'");
    }
}
