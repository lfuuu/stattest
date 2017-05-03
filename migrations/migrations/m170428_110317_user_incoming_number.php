<?php
use app\models\User;

/**
 * Class m170428_110317_user_incoming_number
 */
class m170428_110317_user_incoming_number extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(User::tableName(), 'incoming_phone', $this->string()->notNull()->defaultValue(''));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(User::tableName(), 'incoming_phone');
    }
}
