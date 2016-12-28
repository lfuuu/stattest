<?php

use app\models\Number;

/**
 * Handles the dropping for table `number_site_publish`.
 */
class m161227_161418_drop_number_site_publish extends \app\classes\Migration
{
    private $_field = 'site_publish';

    /**
     * Up
     */
    public function safeUp()
    {
        $this->dropColumn(Number::tableName(), $this->_field);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->addColumn(Number::tableName(), $this->_field, $this->string(1)->defaultValue('N'));
    }
}
