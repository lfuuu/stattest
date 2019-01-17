<?php

use app\models\Number;
use app\models\voip\Registry;

/**
 * Class m190117_145551_voxbone
 */
class m190117_145551_voxbone extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(Number::tableName(), 'source',   "enum('portability','operator','regulator','innonet','voxbone','portability_not_for_sale','operator_not_for_sale') DEFAULT 'operator'");
        $this->alterColumn(Registry::tableName(), 'source', "enum('portability','operator','regulator','innonet','voxbone','portability_not_for_sale','operator_not_for_sale') DEFAULT 'portability'");    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(Number::tableName(), 'source',   "enum('portability','operator','regulator','innonet','boxbone','portability_not_for_sale','operator_not_for_sale') DEFAULT 'operator'");
        $this->alterColumn(Registry::tableName(), 'source', "enum('portability','operator','regulator','innonet','boxbone','portability_not_for_sale','operator_not_for_sale') DEFAULT 'portability'");
    }
}
