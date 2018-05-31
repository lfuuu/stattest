<?php

use app\models\ClientSuper;
use app\models\EntryPoint;

/**
 * Class m180530_151134_client_entry_point
 */
class m180530_151134_client_entry_point extends \app\classes\Migration
{
    private $_field = 'entry_point_id';

    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(ClientSuper::tableName(), $this->_field, $this->integer());

        $this->addForeignKey(
            $this->_getFkName(),
            ClientSuper::tableName(), $this->_field,
            EntryPoint::tableName(),
            'id',
            'SET NULL',
            'CASCADE'

        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropForeignKey($this->_getFkName(), ClientSuper::tableName());
        $this->dropColumn(ClientSuper::tableName(), $this->_field);
    }

    private function _getFkName()
    {
        return 'fk-' . ClientSuper::tableName() . '-' . $this->_field . '-' . EntryPoint::tableName() . '-id';
    }
}
