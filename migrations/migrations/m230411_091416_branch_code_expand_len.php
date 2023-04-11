<?php

/**
 * Class m230411_091416_branch_code_expand_len
 */
class m230411_091416_branch_code_expand_len extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(\app\models\ClientContragent::tableName(), 'branch_code', $this->string(8));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(\app\models\ClientContragent::tableName(), 'branch_code', $this->string(3));
    }
}
