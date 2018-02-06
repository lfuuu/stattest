<?php

use app\models\ActualCallChat;
use app\modules\uu\models\AccountTariff;

/**
 * Class m180206_102950_del_uu_call_chat_actauls
 */
class m180206_102950_del_uu_call_chat_actauls extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->delete(ActualCallChat::tableName(), ['>', 'usage_id', AccountTariff::DELTA]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        // nothing
        // there is no way back
    }
}
