<?php

use app\models\Number;
use app\models\voip\Registry;

/**
 * Class m200120_131923_portability_innonet
 */
class m200120_131923_portability_innonet extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(Number::tableName(), 'source', "enum('portability','operator','regulator','innonet','voxbone','detached','portability_not_for_sale','operator_not_for_sale','portability_innonet') DEFAULT 'operator'");
        $this->alterColumn(Registry::tableName(), 'source', "enum('portability','operator','regulator','innonet','voxbone','detached','portability_not_for_sale','operator_not_for_sale','portability_innonet') DEFAULT 'portability'");
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(Number::tableName(), 'source', "enum('portability','operator','regulator','innonet','voxbone','detached','portability_not_for_sale','operator_not_for_sale') DEFAULT 'operator'");
        $this->alterColumn(Registry::tableName(), 'source', "enum('portability','operator','regulator','innonet','voxbone','detached','portability_not_for_sale','operator_not_for_sale') DEFAULT 'portability'");
    }
}
