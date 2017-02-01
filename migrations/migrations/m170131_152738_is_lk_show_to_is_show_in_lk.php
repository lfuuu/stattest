<?php

/**
 * Class m170131_152738_is_lk_show_to_is_show_in_lk
 */
class m170131_152738_is_lk_show_to_is_show_in_lk extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->renameColumn(\app\models\Bill::tableName(), 'is_lk_show', 'is_show_in_lk');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->renameColumn(\app\models\Bill::tableName(), 'is_show_in_lk', 'is_lk_show');
    }
}
