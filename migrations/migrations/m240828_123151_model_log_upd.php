<?php

/**
 * Class m240828_123151_model_log_upd
 */
class m240828_123151_model_log_upd extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(\app\models\ModelLifeLog::tableName(), 'model_id', $this->bigInteger());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(\app\models\ModelLifeLog::tableName(), 'model_id', $this->integer());
    }
}
