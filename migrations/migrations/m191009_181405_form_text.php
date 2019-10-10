<?php

use app\models\dictionary\FormInfoData;

/**
 * Class m191009_181405_form_text
 */
class m191009_181405_form_text extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(FormInfoData::tableName(), 'text', $this->string(1024));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(FormInfoData::tableName(), 'text');
    }
}
