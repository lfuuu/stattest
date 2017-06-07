<?php
use app\models\Number;
use app\models\voip\Registry;

/**
 * Class m170606_152723_fmc_trunk
 */
class m170606_152723_fmc_trunk extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Registry::tableName(), 'trunk_id', $this->integer());
        $this->addColumn(Number::tableName(), 'trunk_id', $this->integer());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Registry::tableName(), 'trunk_id');
        $this->dropColumn(Number::tableName(), 'trunk_id');
    }
}
