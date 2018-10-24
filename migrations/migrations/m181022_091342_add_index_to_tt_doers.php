<?php

/**
 * Class m181022_091342_add_index_to_tt_doers
 */
class m181022_091342_add_index_to_tt_doers extends \app\classes\Migration
{
    private $_table = 'tt_doers';
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createIndex('idx-' . $this->_table . '-doer_id', $this->_table, 'doer_id');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropIndex('idx-' . $this->_table . '-doer_id', $this->_table);
    }
}
