<?php

class m161002_153643_clean_history extends \app\classes\Migration
{
    /**
     * Удаляем автоматические, ненужные, spam-версии
     */
    public function up()
    {
        $this->delete(\app\models\HistoryVersion::tableName(), ['and', [
            'model' => 'ClientAccount',
            'user_id' => \app\models\User::SYSTEM_USER_ID],
            ['between', 'date', '2016-09-11', '2016-10-03']]
        );
    }

    public function down()
    {
        //nothing
        return true;
    }
}