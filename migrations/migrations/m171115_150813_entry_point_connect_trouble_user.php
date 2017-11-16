<?php

use app\models\EntryPoint;
use app\models\User;

/**
 * Class m171115_150813_entry_point_connect_trouble_user
 */
class m171115_150813_entry_point_connect_trouble_user extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(EntryPoint::tableName(), 'connect_trouble_user_id', $this->integer());
        $this->addForeignKey('fk-' . User::tableName() . '-id', EntryPoint::tableName(), 'connect_trouble_user_id', User::tableName(), 'id');
        $this->update(EntryPoint::tableName(), ['connect_trouble_user_id' => User::DEFAULT_ACCOUNT_MANAGER_USER_ID]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-' . User::tableName() . '-id', EntryPoint::tableName());
        $this->dropColumn(EntryPoint::tableName(), 'connect_trouble_user_id');
    }
}
