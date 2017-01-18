<?php

use app\modules\notifier\models\Logger;

class m161226_131556_notifier_log extends \app\classes\Migration
{

    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableName = Logger::tableName();

        $this->createTable(
            $tableName,
            [
                'id' => $this->primaryKey(),
                'user_id' => $this->integer(11),
                'action' => $this->string(100),
                'value' => $this->string(100),
                'created_at' => $this->dateTime(),
            ],
            'ENGINE=InnoDB CHARSET=utf8'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable(Logger::tableName());
    }

}
