<?php

use app\models\Number;
use app\models\voip\Registry;

/**
 * Class m180919_112803_add_column_to_voip_registry
 */
class m180919_112803_add_column_to_voip_registry extends \app\classes\Migration
{
    private $_nnp_operator_id_column = 'nnp_operator_id';
    private $_usr_operator_id_column = 'usr_operator_id';

    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Registry::tableName(), $this->_nnp_operator_id_column, $this->integer());
        $this->addColumn(Number::tableName(), $this->_nnp_operator_id_column, $this->integer());
        $this->addColumn(Number::tableName(), $this->_usr_operator_id_column, $this->integer());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Registry::tableName(), $this->_nnp_operator_id_column);
        $this->dropColumn(Number::tableName(), $this->_nnp_operator_id_column);
        $this->dropColumn(Number::tableName(), $this->_usr_operator_id_column);
    }
}
