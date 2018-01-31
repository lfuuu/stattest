<?php

use app\modules\mchs\models\MchsMessage;
use app\models\User;
use app\models\UserRight;

/**
 * Class m180129_132343_mchs_rules
 */
class m180129_132343_mchs_rules extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insert(UserRight::tableName(), [
            'resource' => 'mchs',
            'comment' => 'Сообщения от МЧС',
            'values' => 'read,send',
            'values_desc' => 'чтение,отправка',
            'order' => 0,
        ]);

        $this->createTable(MchsMessage::tableName(), [
            'id' => $this->primaryKey(),
            'message' => $this->string(500),
            'date' => $this->dateTime()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'status' => $this->string(1024),
        ]);

        $this->addForeignKey('fk-' . MchsMessage::tableName() . '-user_id-' . User::tableName() . '-id',
            MchsMessage::tableName(), 'user_id',
            User::tableName(), 'id'
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->delete(UserRight::tableName(), ['resource' => 'mchs']);
        $this->dropTable(MchsMessage::tableName());
    }
}
