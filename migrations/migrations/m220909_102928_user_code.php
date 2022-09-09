<?php

use app\classes\Migration;
use app\models\UserFlashCode;

/**
 * Class m220909_102928_user_code
 */
class m220909_102928_user_code extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(UserFlashCode::tableName(), [
            'user_id' => $this->primaryKey(),
            'created_at' => $this->timestamp(),
            'code_md5' => $this->string(32)->notNull(),
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(UserFlashCode::tableName());
    }
}
