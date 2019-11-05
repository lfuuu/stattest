<?php

use app\models\Number;
use app\models\voip\Registry;

/**
 * Class m191105_100722_mvno_partner_id
 */
class m191105_100722_mvno_partner_id extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        foreach([Registry::tableName(), Number::tableName()] as $table) {
            $this->addColumn($table, 'mvno_partner_id', $this->integer());
            $this->update($table, ['mvno_partner_id' => 1], ['mvno_trunk_id' => 588]);
            $this->update($table, ['mvno_partner_id' => 5], ['mvno_trunk_id' => 1231]);
        }
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Registry::tableName(), 'mvno_partner_id');
        $this->dropColumn(Number::tableName(), 'mvno_partner_id');
    }
}
