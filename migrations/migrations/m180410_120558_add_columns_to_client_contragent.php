<?php

use app\models\ClientContragent;

/**
 * Class m180410_120558_add_columns_to_client_contragent
 */
class m180410_120558_add_columns_to_client_contragent extends \app\classes\Migration
{
    private $_column = 'created_at';

    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(ClientContragent::tableName(), $this->_column, $this->dateTime());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(ClientContragent::tableName(), $this->_column);
    }
}
