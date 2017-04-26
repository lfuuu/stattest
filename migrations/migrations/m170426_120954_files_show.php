<?php
use app\models\media\ClientFiles;
use app\models\User;

/**
 * Class m170426_120954_files_show
 */
class m170426_120954_files_show extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(ClientFiles::tableName(), 'is_show_in_lk', $this->integer()->notNull()->defaultValue(0));

        ClientFiles::updateAll(
            ['is_show_in_lk' => 1],
            ['user_id' => User::CLIENT_USER_ID]
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(ClientFiles::tableName(), 'is_show_in_lk');
    }
}
