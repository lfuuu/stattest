<?php

use app\models\Trouble;

/**
 * Class m181025_161712_tt_is_close
 */
class m181025_161712_tt_is_close extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Trouble::tableName(), 'is_closed', $this->tinyInteger()->notNull()->defaultValue(0));
        $this->createIndex('idx-is_closed-cur_stage_id', Trouble::tableName(), ['is_closed', 'cur_stage_id']);
        $this->createIndex('idx-client-is_closed-date_creation', Trouble::tableName(), ['client', 'is_closed','date_creation']);
        $time = $this->beginCommand('Trouble::dao()->setTroublesClosed()');
        Trouble::dao()->setTroublesClosed();
        $this->endCommand($time);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Trouble::tableName(), 'is_closed');
        $this->dropIndex('idx-is_closed-cur_stage_id', Trouble::tableName());
        $this->dropIndex(Trouble::tableName(),'idx-client-is_closed-date_creation');
    }
}
