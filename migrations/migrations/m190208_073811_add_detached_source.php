<?php

use app\models\Number;
use app\models\voip\Registry;

/**
 * Class m190208_073811_add_detached_source
 */
class m190208_073811_add_detached_source extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(Number::tableName(), 'source', "enum('portability','operator','regulator','innonet','voxbone','detached','portability_not_for_sale','operator_not_for_sale') DEFAULT 'operator'");
        $this->alterColumn(Registry::tableName(), 'source', "enum('portability','operator','regulator','innonet','voxbone','detached','portability_not_for_sale','operator_not_for_sale') DEFAULT 'portability'");

    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(Number::tableName(), 'source', "enum('portability','operator','regulator','innonet','voxbone','portability_not_for_sale','operator_not_for_sale') DEFAULT 'operator'");
        $this->alterColumn(Registry::tableName(), 'source', "enum('portability','operator','regulator','innonet','voxbone','portability_not_for_sale','operator_not_for_sale') DEFAULT 'portability'");
    }
}
