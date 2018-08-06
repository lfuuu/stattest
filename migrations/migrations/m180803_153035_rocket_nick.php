<?php

/**
 * Class m180803_153035_rocket_nick
 */
class m180803_153035_rocket_nick extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->renameColumn(\app\models\User::tableName(), 'icq', 'rocket_nick');
        $this->update(\app\models\User::tableName(), ['rocket_nick' =>  '']);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->renameColumn(\app\models\User::tableName(), 'rocket_nick', 'icq');
    }
}
