<?php
use app\models\voip\Registry;

/**
 * Class m170816_140823_add_number_mvno
 */
class m170816_140823_add_number_mvno extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $tableName = \app\models\Number::tableName();
        $this->addColumn($tableName, 'mvno_trunk_id', $this->integer());
        $this->renameColumn($tableName, 'trunk_id', 'fmc_trunk_id');

        $tableName = Registry::tableName();
        $this->addColumn($tableName, 'mvno_trunk_id', $this->integer());
        $this->renameColumn($tableName, 'trunk_id', 'fmc_trunk_id');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $tableName = \app\models\Number::tableName();
        $this->dropColumn($tableName, 'mvno_trunk_id');
        $this->renameColumn($tableName, 'fmc_trunk_id', 'trunk_id');

        $tableName = Registry::tableName();
        $this->dropColumn($tableName, 'mvno_trunk_id');
        $this->renameColumn($tableName, 'fmc_trunk_id', 'trunk_id');
    }
}
