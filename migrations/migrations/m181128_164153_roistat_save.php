<?php

use app\models\TroubleRoistatStore;

/**
 * Class m181128_164153_roistat_save
 */
class m181128_164153_roistat_save extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(TroubleRoistatStore::tableName(), [
            'id' => $this->primaryKey(),
            'account_id' => $this->integer()->notNull()->defaultValue(0),
            'roistat_visit' => $this->string(),
            'created_at' => $this->dateTime()
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(TroubleRoistatStore::tableName());
    }
}
